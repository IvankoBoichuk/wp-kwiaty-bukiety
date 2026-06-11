import type { CartCouponRequest, CartResponse } from './types'
import { WooStoreApiClient } from './client'

export class WooStoreCartCouponsApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async apply(payload: CartCouponRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartCouponRequest>('cart/apply-coupon', payload)
    }

    async remove(payload: CartCouponRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartCouponRequest>('cart/remove-coupon', payload)
    }
}