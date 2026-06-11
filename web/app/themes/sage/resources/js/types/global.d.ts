import type { Alpine as AlpineType } from 'alpinejs';
import type { ProductVariations } from './ProductVariations';
import type { ProductPurchaseConfig } from './ProductPurchaseConfig';
import type { ProductPurchaseStore } from '@/modules/product-core/types';
import type { CartCheckoutConfig, CartCheckoutStoreContract } from '@/modules/cart-checkout/types';

declare module '@alpinejs/mask';

declare module 'alpinejs' {
    interface Stores {
        productPurchase: ProductPurchaseStore;
        cartCheckout: CartCheckoutStoreContract;
    }
}

declare global {
    var Alpine: AlpineType;

    interface Window {
        Alpine: AlpineType;
        cartCheckoutConfig?: CartCheckoutConfig;
        product?: ProductPurchaseConfig;
        productVariations?: ProductVariations;
    }
}

export { };