import type {
    CartAddItemRequest,
    CartRemoveItemRequest,
    CartResponse,
    CartUpdateItemRequest,
} from './types'
import { WooStoreApiClient } from './client'

export class WooStoreCartItemsApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async add<TExtra extends Record<string, unknown> = Record<string, never>>(payload: CartAddItemRequest & TExtra): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartAddItemRequest & TExtra>('cart/add-item', payload)
    }

    async update(payload: CartUpdateItemRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartUpdateItemRequest>('cart/update-item', payload)
    }

    async remove(payload: CartRemoveItemRequest): Promise<CartResponse> {
        return await this.client.post<CartResponse, CartRemoveItemRequest>('cart/remove-item', payload)
    }
}