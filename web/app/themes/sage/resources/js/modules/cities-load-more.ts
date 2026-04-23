type CityRecord = {
    id: number
    name: string
    link: string
}

function normalizeCities(payload: unknown): CityRecord[] {
    if (Array.isArray(payload)) {
        return payload as CityRecord[]
    }

    if (payload && typeof payload === 'object') {
        return Object.values(payload) as CityRecord[]
    }

    return []
}

const WIDTH_PATTERN = [
    'flex-[1_1_28%]',
    'flex-[1_1_35%]',
    'flex-[1_1_25%]',
    'flex-[1_1_30%]',
    'flex-[1_1_32%]',
    'flex-[1_1_27%]',
    'flex-[1_1_33%]',
    'flex-[1_1_29%]',
    'flex-[1_1_26%]',
]

function createCityItem(city: CityRecord, index: number): HTMLLIElement {
    const item = document.createElement('li')
    item.className = WIDTH_PATTERN[index % WIDTH_PATTERN.length]

    const link = document.createElement('a')
    link.href = city.link
    link.className = 'text-h4 flex h-full items-center justify-center rounded-2xl bg-[#E5EFDE] px-4 py-4.5 text-center capitalize'
    link.textContent = city.name

    item.append(link)

    return item
}

function removeLoadMoreButton(button: HTMLButtonElement): void {
    button.closest('[data-cities-load-more]')?.remove()
}

async function loadMoreCities(button: HTMLButtonElement): Promise<void> {
    const list = button.closest('[data-cities-list]')
    if (!(list instanceof HTMLUListElement)) {
        return
    }

    const rawArgs = button.dataset.args
    if (!rawArgs) {
        removeLoadMoreButton(button)
        return
    }

    const args = JSON.parse(rawArgs) as Record<string, string | number | boolean | string[] | number[]>
    const initialCount = Number(button.dataset.initialCount ?? '0')
    const renderedCount = Number(button.dataset.renderedCount ?? '0')
    const totalCount = Number(button.dataset.totalCount ?? '0')

    button.disabled = true

    try {
        const params = new URLSearchParams()

        Object.entries(args).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach((item) => params.append(`${key}[]`, String(item)))
                return
            }

            params.set(key, String(value))
        })

        const response = await fetch(`/wp-json/sage/v1/categories?${params.toString()}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error(`Failed to load cities: ${response.status}`)
        }

        const cities = normalizeCities(await response.json())

        if (cities.length === 0) {
            removeLoadMoreButton(button)
            return
        }

        cities.forEach((city, index) => {
            const item = createCityItem(city, initialCount + renderedCount + index)
            list.insertBefore(item, button.closest('[data-cities-load-more]'))
        })

        const nextRenderedCount = renderedCount + cities.length
        const nextOffset = Number(args.offset ?? 0) + cities.length

        button.dataset.renderedCount = String(nextRenderedCount)
        args.offset = nextOffset
        button.dataset.args = JSON.stringify(args)

        if (nextRenderedCount >= totalCount || cities.length < Number(args.number ?? 6)) {
            removeLoadMoreButton(button)
            return
        }

        button.disabled = false
    } catch (error) {
        console.error(error)
        button.disabled = false
    }
}

export function initCitiesLoadMore(): void {
    const buttons = document.querySelectorAll<HTMLButtonElement>('[data-cities-button]')

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            void loadMoreCities(button)
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCitiesLoadMore)
} else {
    initCitiesLoadMore()
}