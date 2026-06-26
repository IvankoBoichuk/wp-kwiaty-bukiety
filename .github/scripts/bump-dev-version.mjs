import { readFile, writeFile } from 'node:fs/promises';

const token = process.env.GITHUB_TOKEN;
const prNumber = process.env.PR_NUMBER;
const repo = process.env.REPO;

if (!token || !prNumber || !repo) {
    throw new Error('Missing required environment variables: GITHUB_TOKEN, PR_NUMBER, REPO');
}

const [owner, repoName] = repo.split('/');

if (!owner || !repoName) {
    throw new Error(`Invalid REPO value: ${repo}`);
}

async function githubRequest(path) {
    const response = await fetch(`https://api.github.com${path}`, {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/vnd.github+json',
            'User-Agent': 'kwiaty-bukiety-versioning',
            'X-GitHub-Api-Version': '2022-11-28',
        },
    });

    if (!response.ok) {
        const body = await response.text();
        throw new Error(`GitHub API request failed (${response.status}) for ${path}: ${body}`);
    }

    return response.json();
}

async function fetchPullRequest() {
    return githubRequest(`/repos/${owner}/${repoName}/pulls/${prNumber}`);
}

async function fetchPullRequestCommits() {
    const commits = [];
    let page = 1;

    while (true) {
        const batch = await githubRequest(`/repos/${owner}/${repoName}/pulls/${prNumber}/commits?per_page=100&page=${page}`);

        commits.push(...batch);

        if (batch.length < 100) {
            break;
        }

        page += 1;
    }

    return commits;
}

function detectBump(messages) {
    let level = 0;

    for (const message of messages) {
        if (/(^|\n)[a-z]+(\([^)]+\))?!: /i.test(message) || /BREAKING CHANGE:/i.test(message)) {
            level = Math.max(level, 3);
            continue;
        }

        if (/(^|\n)feat(\([^)]+\))?: /i.test(message)) {
            level = Math.max(level, 2);
            continue;
        }

        if (/(^|\n)fix(\([^)]+\))?: /i.test(message)) {
            level = Math.max(level, 1);
        }
    }

    return level;
}

function incrementVersion(version, bumpLevel) {
    const match = version.match(/^(\d+)\.(\d+)\.(\d+)$/);

    if (!match) {
        throw new Error(`Unsupported version format: ${version}`);
    }

    const major = Number(match[1]);
    const minor = Number(match[2]);
    const patch = Number(match[3]);

    if (bumpLevel === 3) {
        return `${major + 1}.0.0`;
    }

    if (bumpLevel === 2) {
        return `${major}.${minor + 1}.0`;
    }

    if (bumpLevel === 1) {
        return `${major}.${minor}.${patch + 1}`;
    }

    return version;
}

function stripConventionalPrefix(message) {
    const firstLine = message.split('\n')[0].trim();
    return firstLine.replace(/^[a-z]+(\([^)]+\))?!?:\s*/i, '');
}

async function main() {
    const pr = await fetchPullRequest();
    const commits = await fetchPullRequestCommits();
    const messages = commits.map((commit) => commit.commit.message.trim()).filter(Boolean);
    const bumpLevel = detectBump(messages);

    if (bumpLevel === 0) {
        console.log('No feat/fix/breaking commits found. Skipping version bump.');
        return;
    }

    const composerPath = 'composer.json';
    const stylePath = 'web/app/themes/sage/style.css';

    const composerRaw = await readFile(composerPath, 'utf8');
    const composerJson = JSON.parse(composerRaw);
    const currentVersion = composerJson.version;

    if (!currentVersion) {
        throw new Error('composer.json is missing a version field');
    }

    const nextVersion = incrementVersion(currentVersion, bumpLevel);

    if (nextVersion === currentVersion) {
        console.log(`Version remains unchanged at ${currentVersion}.`);
        return;
    }

    composerJson.version = nextVersion;
    await writeFile(composerPath, `${JSON.stringify(composerJson, null, 2)}\n`);

    const styleRaw = await readFile(stylePath, 'utf8');
    const updatedStyle = styleRaw.replace(/^(Version:\s*).+$/m, `$1${nextVersion}`);

    if (updatedStyle === styleRaw) {
        throw new Error(`Failed to update Version header in ${stylePath}`);
    }

    await writeFile(stylePath, updatedStyle);

    console.log(`Bumped version ${currentVersion} -> ${nextVersion} for PR #${pr.number}`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
