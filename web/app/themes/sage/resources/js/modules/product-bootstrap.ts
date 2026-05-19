import Alpine from 'alpinejs'
import { ProductStore } from './product-core/ProductStore'
import { AdditionsPlugin } from './product-core/plugins/AdditionsPlugin'
import { DeliveryPlugin } from './product-core/plugins/DeliveryPlugin'
import { VariationsPlugin } from './product-core/plugins/VariationsPlugin'

function init(): void {
    if (!window.product) return

    // Initialize the product store with the global product configuration
    const store = new ProductStore(window.product)

    // Register the product purchase store in Alpine for global access
    Alpine.store("productPurchase", store)

    // Initialize and register plugins with the product store
    const alpineStore = Alpine.store("productPurchase");

    store.registerPlugin(new DeliveryPlugin())
    store.registerPlugin(new AdditionsPlugin())

    if (window.product.isVariable) {
        store.registerPlugin(new VariationsPlugin())
    }

    store.initPlugins(alpineStore)
}

document.addEventListener('alpine:init', init)