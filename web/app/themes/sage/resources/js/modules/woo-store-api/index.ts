import { WooStoreCartApi } from './cart'
import { WooStoreCartCouponsApi } from './cart-coupons'
import { WooStoreCartItemsApi } from './cart-items'
import { WooStoreCheckoutApi } from './checkout'
import { WooStoreCheckoutOrderApi } from './checkout-order'
import { WooStoreApiClient } from './client'
import { WooStoreOrderApi } from './order'
import type { StoreApiClientOptions } from './types'

export class WooStoreApiToolkit {
    readonly client
    readonly cart
    readonly cartCoupons
    readonly cartItems
    readonly checkout
    readonly checkoutOrder
    readonly order

    constructor(options: StoreApiClientOptions = {}) {
        this.client = new WooStoreApiClient(options)
        this.cart = new WooStoreCartApi(this.client)
        this.cartCoupons = new WooStoreCartCouponsApi(this.client)
        this.cartItems = new WooStoreCartItemsApi(this.client)
        this.checkout = new WooStoreCheckoutApi(this.client)
        this.checkoutOrder = new WooStoreCheckoutOrderApi(this.client)
        this.order = new WooStoreOrderApi(this.client)
    }

    getNonce(): string {
        return this.client.getNonce()
    }

    setNonce(nonce: string): void {
        this.client.setNonce(nonce)
    }

    getCartToken(): string {
        return this.client.getCartToken()
    }

    setCartToken(cartToken: string): void {
        this.client.setCartToken(cartToken)
    }
}

export function createWooStoreApiToolkit(options: StoreApiClientOptions = {}): WooStoreApiToolkit {
    return new WooStoreApiToolkit(options)
}

export { WooStoreApiClient, WooStoreApiError } from './client'
export * from './types'