import {
    Content,
    Media,
    defaultMediaValue,
    RepeaterField,
    registerSageBlock
} from './shared';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { createElement, Fragment } from '@wordpress/element';
import { ServerSideRender } from '@wordpress/server-side-render';
import type { BlockAttribute } from '@wordpress/blocks';
import type { MediaValue } from './shared';

type ProductsLayout = 'default' | 'popular';

type ProductsButton = {
    text: string;
    link: string;
    variant: string;
    size: string;
    target: string;
    showIcon: boolean;
};

type ProductsBlockData = {
    layout?: ProductsLayout;
    texts?: {
        title?: string;
        subtitle?: string;
        text?: string;
        advantages?: string[];
    };
    media?: MediaValue;
    products?: number[];
    buttons?: ProductsButton[];
};

const defaultButton: ProductsButton = {
    text: '',
    link: '',
    variant: 'green',
    size: 'lg',
    target: '_self',
    showIcon: false,
};

const productsAttributes = {
    layout: {
        type: 'string',
        default: 'default' as ProductsLayout,
    },
    texts: {
        type: 'object',
        default: {} as ProductsBlockData['texts'],
    },
    media: {
        type: 'object',
        default: defaultMediaValue,
    },
    products: {
        type: 'array',
        default: [] as number[],
    },
    buttons: {
        type: 'array',
        default: [defaultButton] as ProductsButton[],
    },
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof productsAttributes>({
    slug: 'products',
    title: 'Products',
    icon: 'images-alt2',
    description: 'Product slider based on a product ID list.',
    attributes: productsAttributes,
    includeBaseAttributes: false,
    variations: [
        {
            name: 'default',
            title: 'Products',
            description: 'Slider layout for selected product IDs.',
            isDefault: true,
            scope: ['block', 'inserter', 'transform'],
            isActive: ['layout'],
            attributes: {
                layout: 'default',
                buttons: [
                    defaultButton,
                ],
            },
        },
        {
            name: 'popular',
            title: 'Products Popular',
            description: 'Grid layout with heading and button for selected product IDs.',
            scope: ['block', 'inserter', 'transform'],
            isActive: ['layout'],
            attributes: {
                layout: 'popular',
                buttons: [
                    defaultButton,
                ],
            },
        },
    ],
    save: () => {
        return null;
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();

        return <>
            <InspectorControls>
                <Content
                    data={attributes.texts}
                    setAttributes={setAttributes}
                    attributePath={['texts']}
                />

                <Media
                    value={attributes.media}
                    initialOpen={false}
                    onChange={(media) => setAttributes({ media })}
                />

                <RepeaterField
                    title="Products"
                    initialOpen={false}
                    items={attributes.products}
                    onChange={(products) => setAttributes({ products })}
                    entityKind="postType"
                    entityName="product"
                    query={{
                        per_page: -1,
                    }}
                    selectLabel="Product"
                    placeholder="Select product"
                    help="The first product is used as the featured tile on the left."
                    addButtonLabel="Add product"
                    renderItemLabel={(item, index) => item ? (index === 0 ? `Featured product #${item}` : `Product #${item}`) : (index === 0 ? 'Featured product' : `Product ${index + 1}`)}
                    getOptionLabel={(record) => record.slug ? `${record.name ?? ''} (${record.slug})` : String(record.name ?? '')}
                />
            </InspectorControls>

            <div {...blockProps}>
                <ServerSideRender
                    block="sage/products"
                    httpMethod="POST"
                    attributes={{
                        layout: attributes.layout,
                        texts: attributes.texts,
                        media: attributes.media,
                        products: attributes.products,
                        buttons: attributes.buttons,
                    }}
                />
            </div>
        </>;
    },
});