import type { CheckoutOrderRequest, CheckoutResponse } from './types'
import { WooStoreApiClient } from './client'

export class WooStoreCheckoutOrderApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async process(payload: CheckoutOrderRequest): Promise<CheckoutResponse> {
        return await this.client.post<CheckoutResponse, CheckoutOrderRequest>('checkout', payload)
    }
}