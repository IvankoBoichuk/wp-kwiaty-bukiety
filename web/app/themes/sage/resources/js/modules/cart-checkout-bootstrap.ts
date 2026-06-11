import Alpine from 'alpinejs'
import { CartCheckoutStore } from './cart-checkout/CartCheckoutStore'

function init(): void {
    if (!window.cartCheckoutConfig) return

    const store = new CartCheckoutStore(window.cartCheckoutConfig)

    Alpine.store('cartCheckout', store)
}

document.addEventListener('alpine:init', init)