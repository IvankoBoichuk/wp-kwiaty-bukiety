import type { CartPayload, CartResponse } from './types'

export async function addProductToCart(payload: CartPayload): Promise<CartResponse> {
    const response = await fetch('/wp-json/sage/v1/cart/add', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify(payload),
    })

    const result = await response.json() as CartResponse

    if (!response.ok || result.status !== 'ok') {
        throw new Error(result.message || 'Unable to add product to cart.')
    }

    return result
}