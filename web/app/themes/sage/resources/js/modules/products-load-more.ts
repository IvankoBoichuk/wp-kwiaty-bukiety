function removeLoadMoreControl(button: HTMLButtonElement): void {
    button.closest('[data-products-load-more]')?.remove()
}

function getButtonLabelElement(button: HTMLButtonElement): HTMLSpanElement | null {
    const label = button.querySelector('span')

    return label instanceof HTMLSpanElement ? label : null
}

function setButtonState(button: HTMLButtonElement, disabled: boolean): void {
    button.disabled = disabled

    const label = getButtonLabelElement(button)
    if (!label) {
        return
    }

    label.textContent = disabled
        ? button.dataset.loadingLabel ?? button.dataset.defaultLabel ?? ''
        : button.dataset.defaultLabel ?? label.textContent ?? ''
}

function parseProductsDocument(html: string): {
    items: HTMLLIElement[]
    nextUrl: string
} | null {
    const parser = new DOMParser()
    const document = parser.parseFromString(html, 'text/html')
    const list = document.querySelector('[data-products-list]')

    if (!(list instanceof HTMLUListElement)) {
        return null
    }

    const items = Array.from(list.children).filter(
        (item): item is HTMLLIElement => item instanceof HTMLLIElement,
    )

    const nextButton = document.querySelector<HTMLButtonElement>(
        '[data-products-load-more-button]',
    )

    return {
        items,
        nextUrl: nextButton?.dataset.nextUrl ?? '',
    }
}

async function loadMoreProducts(button: HTMLButtonElement): Promise<void> {
    const list = document.querySelector('[data-products-list]')
    if (!(list instanceof HTMLUListElement)) {
        removeLoadMoreControl(button)
        return
    }

    const nextUrl = button.dataset.nextUrl
    if (!nextUrl) {
        removeLoadMoreControl(button)
        return
    }

    setButtonState(button, true)

    try {
        const response = await fetch(nextUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!response.ok) {
            throw new Error(`Failed to load products: ${response.status}`)
        }

        const payload = parseProductsDocument(await response.text())

        if (!payload || payload.items.length === 0) {
            removeLoadMoreControl(button)
            return
        }

        payload.items.forEach((item) => {
            list.append(item)
        })

        if (payload.nextUrl === '') {
            removeLoadMoreControl(button)
            return
        }

        button.dataset.nextUrl = payload.nextUrl
        setButtonState(button, false)
    } catch (error) {
        console.error(error)
        setButtonState(button, false)
    }
}

function initProductsLoadMore(): void {
    const buttons = document.querySelectorAll<HTMLButtonElement>(
        '[data-products-load-more-button]',
    )

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            void loadMoreProducts(button)
        })
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductsLoadMore)
} else {
    initProductsLoadMore()
}