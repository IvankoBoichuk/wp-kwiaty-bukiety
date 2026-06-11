import type { ProductPlugin, ProductPurchaseAddition, ProductPurchaseStore } from '../types'

const PAID_CARD_MESSAGE_SELECTOR = '#card_message_payed'
const PAID_CARD_MESSAGE_PRICE = 20
const PAID_CARD_MESSAGE_ADDITION_ID = -20

export class PaidCardMessagePlugin implements ProductPlugin {
    pluginName = 'paidCardMessage'
    #input: HTMLInputElement | null = null
    #store: ProductPurchaseStore | null = null

    init(store: ProductPurchaseStore): void {
        this.#input = document.querySelector<HTMLInputElement>(PAID_CARD_MESSAGE_SELECTOR)

        if (!this.#input) {
            return
        }

        this.#store = store
        this.syncState()
        this.#input.addEventListener('input', this.syncState)
    }

    destroy(): void {
        this.#input?.removeEventListener('input', this.syncState)
        this.#input = null
        this.#store = null
    }

    private syncState = (): void => {
        if (!this.#input || !this.#store) {
            return
        }

        const value = this.#input.value
        const hasPaidMessage = value.trim() !== ''

        this.#store.setCardMessage(value)

        const paidCardMessageAddition: ProductPurchaseAddition = {
            id: PAID_CARD_MESSAGE_ADDITION_ID,
            name: 'Ribbon message',
            price: PAID_CARD_MESSAGE_PRICE,
            includeInPayload: false,
        }

        this.#store.setAddition(paidCardMessageAddition, hasPaidMessage)
    }
}