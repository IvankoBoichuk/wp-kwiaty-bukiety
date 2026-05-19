import type { ProductVariation } from "@/types/ProductVariations"

export interface ProductPurchaseAddition {
    id: number
    name: string
    price: number
}

export interface ProductPurchaseStore {
    isReady: boolean
    isSubmitting: boolean
    productId: number
    basePrice: number
    currencySymbol: string
    isVariable: boolean
    variation: ProductVariation | null
    quantity: number
    // selectedVariationId: number | null
    // selectedVariation: ProductVariation | null
    additions: ProductPurchaseAddition[]
    deliveryDate: string
    deliveryTime: string
    cardMessage: string
    unitPrice: number
    totalPrice: number
    formattedTotal: string
    canSubmit: boolean
    increment(): void
    decrement(): void
    setQuantity(value: number): void
    // setVariation(variation: ProductVariation): void
    setAddition(addition: ProductPurchaseAddition, isSelected: boolean): void
    hasAddition(additionId: number): boolean
    setDeliveryDate(value: string): void
    setDeliveryTime(value: string): void
    setCardMessage(value: string): void
    submit(): Promise<void>
}

export interface CartPayload {
    productId: number
    quantity: number
    variationId: number | null
    attributes: Record<string, string>
    deliveryDate: string
    deliveryTime: string
    cardMessage: string
    additionIds: number[]
}

export interface CartResponse {
    status?: string
    message?: string
    cartCount?: number
    cartUrl?: string
}

export interface ProductPlugin {
    pluginName: string
    init(store: ProductPurchaseStore): void
    destroy?(): void
}
