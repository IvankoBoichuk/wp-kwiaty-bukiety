import {
	Content,
	Media,
	defaultMediaValue,
	RepeaterField,
	registerSageBlock,
} from './shared';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { createElement, Fragment } from '@wordpress/element';
import { ServerSideRender } from '@wordpress/server-side-render';
import type { BlockAttribute } from '@wordpress/blocks';
import type { MediaValue } from './shared';

type ReviewsBlockData = {
	texts?: {
		title?: string;
		subtitle?: string;
		text?: string;
	};
	media?: MediaValue;
	reviews?: number[];
};

const reviewsAttributes = {
	texts: {
		type: 'object',
		default: {} as ReviewsBlockData['texts'],
	},
	media: {
		type: 'object',
		default: defaultMediaValue,
	},
	reviews: {
		type: 'array',
		default: [] as number[],
	},
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof reviewsAttributes>({
	slug: 'reviews',
	title: 'Reviews',
	icon: 'star-filled',
	description: 'WooCommerce product reviews selected by review ID.',
	attributes: reviewsAttributes,
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
				/>

				<Media
					value={attributes.media}
					initialOpen={false}
					onChange={(media) => setAttributes({ media })}
				/>

				<RepeaterField
					title="Reviews"
					initialOpen={false}
					items={attributes.reviews}
					onChange={(reviews) => setAttributes({ reviews })}
					entityKind="root"
					entityName="comment"
					query={{
						per_page: -1,
						post_type: 'product',
						status: 'approve',
						orderby: 'date_gmt',
						order: 'desc',
					}}
					selectLabel="Review"
					placeholder="Select review"
					help="Select WooCommerce product review comments by ID."
					addButtonLabel="Add review"
					renderItemLabel={(item, index) => item ? `Review #${item}` : `Review ${index + 1}`}
					getOptionLabel={(record) => {
						const author = typeof record.name === 'string'
							? record.name
							: typeof record.title === 'string'
								? record.title
								: record.title?.rendered ?? '';

						const content = typeof (record as { content?: { rendered?: string } | string }).content === 'string'
							? (record as { content?: string }).content ?? ''
							: (record as { content?: { rendered?: string } }).content?.rendered ?? '';

						const excerpt = content
							.replace(/<[^>]+>/g, ' ')
							.replace(/\s+/g, ' ')
							.trim()
							.slice(0, 40);

						return excerpt !== '' ? `${author} (${record.id ?? ''}) - ${excerpt}` : `${author} (${record.id ?? ''})`;
					}}
				/>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="sage/reviews"
					httpMethod="POST"
					attributes={{
						texts: attributes.texts,
						media: attributes.media,
						reviews: attributes.reviews,
					}}
				/>
			</div>
		</>;
	},
});
