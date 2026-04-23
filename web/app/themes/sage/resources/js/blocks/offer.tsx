import {
  Content,
  LinesField,
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

type OfferBlockData = {
  texts?: {
    title?: string;
    text?: string;
    advantages?: string[];
  };
  media?: MediaValue;
  categories?: number[];
};

const offerAttributes = {
  texts: {
    type: 'object',
    default: {} as OfferBlockData['texts'],
  },
  media: {
    type: 'object',
    default: defaultMediaValue,
  },
  categories: {
    type: 'array',
    default: [] as number[],
  },
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof offerAttributes>({
  slug: 'offer',
  title: 'Offer',
  icon: 'cover-image',
  description: 'Hero section for the homepage.',
  attributes: offerAttributes,
  includeBaseAttributes: false,
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
        >
          {LinesField({
            label: 'Advantages',
            help: 'One line = one badge',
            value: attributes.texts?.advantages,
            onChange: (value: string[]) => setAttributes({ texts: { ...attributes.texts, advantages: value } }),
          })}
        </Content>

        <Media
          value={attributes.media}
          initialOpen={false}
          onChange={(media) => setAttributes({ media })}
        />

        <RepeaterField
          title="Categories"
          initialOpen={false}
          items={attributes.categories}
          onChange={(categories) => setAttributes({ categories })}
          entityKind="taxonomy"
          entityName="product_cat"
          query={{
            per_page: -1,
            orderby: 'name',
            order: 'asc',
          }}
          selectLabel="Category"
          placeholder="Select category"
          help="The first category is used as the featured tile on the left."
          addButtonLabel="Add category"
          renderItemLabel={(item, index) => item ? (index === 0 ? `Featured category #${item}` : `Category #${item}`) : (index === 0 ? 'Featured category' : `Category ${index + 1}`)}
          getOptionLabel={(record) => record.slug ? `${record.name ?? ''} (${record.slug})` : String(record.name ?? '')}
        />
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="sage/offer"
          httpMethod="POST"
          attributes={{
            texts: attributes.texts,
            media: attributes.media,
            categories: attributes.categories,
          }}
        />
      </div>
    </>;
  },
});