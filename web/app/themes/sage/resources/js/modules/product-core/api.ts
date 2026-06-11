import { createWooStoreApiToolkit } from '../woo-store-api'
import type { StoreApiItemVariation } from '../woo-store-api'
import type { CartPayload, CartResponse } from './types'

let wooStoreApi = createWooStoreApiToolkit()

function normalizeVariation(attributes: CartPayload['attributes']): StoreApiItemVariation[] {
    return Object.entries(attributes)
        .filter(([, value]) => value !== '')
        .map(([attribute, value]) => ({
            attribute,
            value,
        }))
}

export function configureProductWooStoreApi(nonce: string): void {
    if (nonce === '') {
        return
    }

    wooStoreApi.setNonce(nonce)
}

export async function addProductToCart(payload: CartPayload): Promise<CartResponse> {
    const response = await wooStoreApi.cartItems.add({
        id: payload.variationId || payload.productId,
        quantity: payload.quantity,
        variation: normalizeVariation(payload.attributes),
        deliveryDate: payload.deliveryDate,
        deliveryTime: payload.deliveryTime,
        cardMessage: payload.cardMessage,
        additionIds: payload.additionIds,
    })

    return {
        status: 'ok',
        cartCount: response.items_count || response.items.reduce((total, item) => total + item.quantity, 0),
        cartUrl: '/cart/',
    }
}