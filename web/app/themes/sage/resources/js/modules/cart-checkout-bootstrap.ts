import Alpine from 'alpinejs'
import { CartCheckoutStore } from './cart-checkout/CartCheckoutStore'
import { initPostalCodeAutocomplete } from './postal-code-autocomplete'

function init(): void {
    if (!window.cartCheckoutConfig) return

    const store = new CartCheckoutStore(window.cartCheckoutConfig)

    Alpine.store('cartCheckout', store)
}

document.addEventListener('alpine:init', init)
document.addEventListener('DOMContentLoaded', () => {
    if (!window.cartCheckoutConfig) return
    initPostalCodeAutocomplete()
})