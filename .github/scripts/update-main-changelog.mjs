import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';

function git(...args) {
    return execFileSync('git', args, { encoding: 'utf8' }).trim();
}

function readVersion() {
    const composerJson = JSON.parse(execFileSync('node', ['-p', "JSON.stringify(require('./composer.json'))"], { encoding: 'utf8' }));
    if (!composerJson.version) {
        throw new Error('composer.json is missing a version field');
    }

    return composerJson.version;
}

function getPreviousTag() {
    try {
        return git('describe', '--tags', '--abbrev=0');
    } catch {
        return '';
    }
}

function getCommitMessages(previousTag) {
    const range = previousTag ? `${previousTag}..HEAD` : 'HEAD';
    const output = git('log', range, '--format=%B%x1e');

    return output
        .split('\u001e')
        .map((message) => message.trim())
        .filter(Boolean)
        .filter((message) => !/^docs\(changelog\): update release notes/i.test(message))
        .filter((message) => !/^chore\(release\): bump dev version/i.test(message));
}

function stripConventionalPrefix(message) {
    const firstLine = message.split('\n')[0].trim();
    return firstLine.replace(/^[a-z]+(\([^)]+\))?!?:\s*/i, '');
}

function classifyMessage(message) {
    if (/(^|\n)[a-z]+(\([^)]+\))?!: /i.test(message) || /BREAKING CHANGE:/i.test(message)) {
        return 'Breaking';
    }

    if (/(^|\n)feat(\([^)]+\))?: /i.test(message)) {
        return 'Features';
    }

    if (/(^|\n)fix(\([^)]+\))?: /i.test(message)) {
        return 'Fixes';
    }

    return 'Other';
}

function buildSection(version, messages) {
    const date = new Date().toISOString().slice(0, 10);
    const groups = {
        Breaking: [],
        Features: [],
        Fixes: [],
        Other: [],
    };

    for (const message of messages) {
        groups[classifyMessage(message)].push(stripConventionalPrefix(message));
    }

    const lines = [`## ${version} - ${date}`, ''];

    for (const [title, entries] of Object.entries(groups)) {
        if (entries.length === 0) {
            continue;
        }

        lines.push(`### ${title}`);
        for (const entry of entries) {
            lines.push(`- ${entry}`);
        }
        lines.push('');
    }

    return lines.join('\n').trim();
}

function mergeChangelog(current, section, version) {
    const normalized = current.trim();
    if (normalized.includes(`## ${version} - `)) {
        return `${normalized}\n`;
    }

    const header = '# Changelog';
    const intro = 'All notable changes to this project will be documented in this file.';

    if (!normalized) {
        return [header, '', intro, '', section, ''].join('\n');
    }

    const lines = normalized.split('\n');
    const startsWithHeader = lines[0]?.trim() === header;

    if (!startsWithHeader) {
        return [header, '', intro, '', section, '', normalized, ''].join('\n');
    }

    const remainder = normalized.slice(header.length).trimStart();
    const withoutIntro = remainder.startsWith(intro) ? remainder.slice(intro.length).trimStart() : remainder;

    return [header, '', intro, '', section, '', withoutIntro].filter(Boolean).join('\n') + '\n';
}

async function main() {
    const version = readVersion();
    const previousTag = getPreviousTag();
    const messages = getCommitMessages(previousTag);

    if (messages.length === 0) {
        console.log('No release notes changes found since the previous tag.');
        return;
    }

    const changelogPath = 'CHANGELOG.md';
    const currentChangelog = await readFile(changelogPath, 'utf8');
    const section = buildSection(version, messages);
    const updatedChangelog = mergeChangelog(currentChangelog, section, version);

    await writeFile(changelogPath, updatedChangelog);
    console.log(`Updated changelog for v${version}`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
