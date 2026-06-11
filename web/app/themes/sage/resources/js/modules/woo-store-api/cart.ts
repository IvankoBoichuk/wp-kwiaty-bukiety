import type {
    CartResponse,
    CartSelectShippingRateRequest,
    CartUpdateCustomerRequest,
} from './types'
import { WooStoreApiClient } from './client'

export class WooStoreCartApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async get(): Promise<CartResponse> {
        return await this.client.get<CartResponse>('cart')
    }

    async updateCustomer(payload: CartUpdateCustomerRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartUpdateCustomerRequest>('cart/update-customer', payload)
    }

    async selectShippingRate(payload: CartSelectShippingRateRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartSelectShippingRateRequest>('cart/select-shipping-rate', payload)
    }
}