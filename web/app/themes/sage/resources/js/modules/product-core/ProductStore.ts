import type { ProductPurchaseConfig } from '@/types/ProductPurchaseConfig'
import { addProductToCart } from './api'
import type {
    CartPayload,
    ProductPlugin,
    ProductPurchaseAddition,
    ProductPurchaseStore
} from './types'
import type { ProductVariation } from '@/types/ProductVariations'
import { animateCSS } from '../animate'

function formatPrice(amount: number, currencySymbol: string): string {
    const hasFraction = !Number.isInteger(amount)

    return `${new Intl.NumberFormat('pl-PL', {
        minimumFractionDigits: hasFraction ? 2 : 0,
        maximumFractionDigits: 2,
    }).format(amount)} ${currencySymbol}`
}

export class ProductStore implements ProductPurchaseStore {
    _basePrice: number
    isReady = true
    isSubmitting = false
    productId: number
    currencySymbol: string
    isVariable: boolean
    quantity = 1
    additions: ProductPurchaseAddition[] = []
    deliveryDate = ''
    deliveryTime = ''
    cardMessage = ''
    _variation: ProductVariation | null = null

    readonly #plugins = new Map<string, ProductPlugin>()

    constructor(config: ProductPurchaseConfig) {

        this.productId = config.productId
        this._basePrice = config.basePrice
        this.currencySymbol = config.currencySymbol
        this.isVariable = config.isVariable
    }

    get basePrice(): number {
        return this._basePrice
    }

    set basePrice(value: number) {
        this._basePrice = value
    }

    set variation(variation: ProductVariation | null) {
        this._variation = variation

        if (variation) {
            this.basePrice = variation.display_price
            this.isVariable = true
        }
    }

    get variation(): ProductVariation | null {
        return this._variation
    }

    get unitPrice(): number {
        return this._basePrice
    }

    get totalPrice(): number {
        const additionsTotal = this.additions.reduce((total, addition) => total + addition.price, 0)
        return (this.unitPrice + additionsTotal) * this.quantity
    }

    get formattedTotal(): string {
        return formatPrice(this.totalPrice, this.currencySymbol)
    }

    get canSubmit(): boolean {
        return true
    }

    increment(): void {
        this.quantity += 1
    }

    decrement(): void {
        this.quantity = Math.max(1, this.quantity - 1)
    }

    setQuantity(value: number): void {
        this.quantity = Math.max(1, Math.floor(value) || 1)
    }

    setAddition(addition: ProductPurchaseAddition, isSelected: boolean): void {
        const additions = this.additions.filter((item) => item.id !== addition.id)
        this.additions = isSelected ? [...additions, addition] : additions
    }

    hasAddition(additionId: number): boolean {
        return this.additions.some((addition) => addition.id === additionId)
    }

    setDeliveryDate(value: string): void {
        this.deliveryDate = value
    }

    setDeliveryTime(value: string): void {
        this.deliveryTime = value
    }

    setCardMessage(value: string): void {
        this.cardMessage = value
    }

    getCartPayload(): CartPayload {
        return {
            productId: this.productId,
            quantity: this.quantity,
            variationId: this._variation ? this._variation.variation_id : null,
            attributes: {},
            deliveryDate: this.deliveryDate,
            deliveryTime: this.deliveryTime,
            cardMessage: this.cardMessage,
            additionIds: this.additions.map((addition) => addition.id),
        }
    }

    registerPlugin(plugin: ProductPlugin): void {
        this.#plugins.set(plugin.pluginName, plugin)
    }

    getPlugin(name: string): ProductPlugin | undefined {
        return this.#plugins.get(name)
    }

    initPlugins(store: ProductPurchaseStore): void {
        this.#plugins.forEach((plugin) => {
            plugin.init(store)
        })
    }

    destroy(): void {
        this.#plugins.forEach((plugin) => {
            plugin.destroy?.()
        })

        this.#plugins.clear()
    }

    async submit(): Promise<void> {
        if (this.isSubmitting || !this.canSubmit) {
            return
        }

        this.isSubmitting = true

        try {
            const result = await addProductToCart(this.getCartPayload())
            document.querySelectorAll<HTMLElement>('.counter-for-cart').forEach((element) => {
                element.querySelector('[data-count]')?.setAttribute('data-count', String(result.cartCount || 0));
                animateCSS(element, 'heartBeat')
            })
        } catch (error) {
            window.alert(error instanceof Error ? error.message : 'Unable to add product to cart.')
        } finally {
            this.isSubmitting = false
        }
    }
}