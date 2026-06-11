import type { OrderRequest, OrderResponse } from './types'
import { WooStoreApiClient } from './client'

export class WooStoreOrderApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async get(payload: OrderRequest): Promise<OrderResponse> {
        return await this.client.get<OrderResponse>(`order/${payload.id}`, {
            query: {
                key: payload.key,
                billing_email: payload.billing_email,
            },
        })
    }
}