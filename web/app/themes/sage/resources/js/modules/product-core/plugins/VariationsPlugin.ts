import type { ProductVariation } from '../../../types/ProductVariations'

import type { ProductPlugin, ProductPurchaseStore } from '../types'

const ACTIVE_CLASS = 'active'

export class VariationsPlugin implements ProductPlugin {
    pluginName = 'variations'
    #buttonsWrapper: HTMLElement | null = null
    #buttons: HTMLButtonElement[] = []
    #variations: ProductVariation[] = []
    #store: ProductPurchaseStore | null = null

    init(store: ProductPurchaseStore): void {
        this.#store = store

        if (!window.productVariations || window.productVariations.length === 0) {
            console.error('VariationsPlugin: No product variations found on the page.');
            return
        }

        this.#variations = window.productVariations
        this.#buttonsWrapper = document.getElementById('product-variations');

        if (!this.#buttonsWrapper) {
            console.error('VariationsPlugin: No buttons wrapper found on the page.');
            return
        }

        this.#buttons = Array.from(this.#buttonsWrapper.querySelectorAll<HTMLButtonElement>('[data-variation-id]'));

        if (this.#buttons.length === 0) {
            console.error('VariationsPlugin: No variation buttons found on the page.');
            return
        }

        this.#buttonsWrapper.addEventListener('click', this.createClickHandler)

        this.applyVariation(this.#variations[0], this.#buttons[0])
    }

    destroy(): void {
        this.#buttonsWrapper?.removeEventListener('click', this.createClickHandler)
        this.#buttonsWrapper = null
        this.#buttons = []
        this.#variations = []
    }

    private createClickHandler = (event: PointerEvent): void => {
        const button = (event.target as HTMLElement).closest<HTMLButtonElement>('[data-variation-id]')

        if (!button) return

        const variationId = Number(button.dataset.variationId)
        if (!Number.isInteger(variationId) || variationId <= 0) return

        const variation = this.getVariationById(variationId)

        if (!variation) return

        this.applyVariation(variation, button)
    }

    private applyVariation(variation: ProductVariation, button: HTMLButtonElement): void {
        this.setActiveButton(button)

        if (this.#store) {
            this.#store.variation = variation
        }

        window.dispatchEvent(new CustomEvent<ProductVariation>('product:variation-change', {
            detail: variation,
        }))
    }

    private getVariationById(variationId: number): ProductVariation | undefined {
        return this.#variations.find((variation) => variation.variation_id === variationId)
    }

    private setActiveButton(activeButton: HTMLButtonElement): void {
        this.#buttons.forEach((button) => {
            button.classList.toggle(ACTIVE_CLASS, button === activeButton)
            button.setAttribute('aria-pressed', button === activeButton ? 'true' : 'false')
        })
    }
}