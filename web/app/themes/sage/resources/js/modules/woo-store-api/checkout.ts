import type { CheckoutResponse, CheckoutUpdateRequest } from './types'
import { WooStoreApiClient } from './client'

export class WooStoreCheckoutApi {
    constructor(protected readonly client: WooStoreApiClient) { }

    async get(): Promise<CheckoutResponse> {
        return await this.client.get<CheckoutResponse>('checkout')
    }

    async update(payload: CheckoutUpdateRequest, options: { calcTotals?: boolean } = {}): Promise<CheckoutResponse> {
        return await this.client.put<CheckoutResponse, CheckoutUpdateRequest>('checkout', payload, {
            query: options.calcTotals === undefined
                ? undefined
                : { __experimental_calc_totals: options.calcTotals },
        })
    }
}