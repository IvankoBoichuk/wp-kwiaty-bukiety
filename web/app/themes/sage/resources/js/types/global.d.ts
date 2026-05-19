import type { Alpine as AlpineType } from 'alpinejs';
import type { ProductVariations } from './ProductVariations';
import type { ProductPurchaseConfig } from './ProductPurchaseConfig';
import type { ProductPurchaseStore } from '@/modules/product-core/types';

declare module 'alpinejs' {
    interface Stores {
        productPurchase: ProductPurchaseStore;
    }
}

declare global {
    var Alpine: AlpineType;

    interface Window {
        Alpine: AlpineType;
        product?: ProductPurchaseConfig;
        productVariations?: ProductVariations;
    }
}

export { };