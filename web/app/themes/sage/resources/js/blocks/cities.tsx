import {
  Content,
  Media,
  RepeaterField,
  defaultMediaValue,
  registerSageBlock,
} from './shared';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  Button,
  Notice,
  PanelBody,
  SelectControl,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { createElement, Fragment } from '@wordpress/element';
import { ServerSideRender } from '@wordpress/server-side-render';
import type { BlockAttribute } from '@wordpress/blocks';
import type { MediaValue } from './shared';

type ButtonVariant = 'green' | 'purple' | 'white';
type ButtonSize = 'lg' | 'md' | 'sm';
type ButtonTarget = '_self' | '_blank';

type CitiesButton = {
  text: string;
  link: string;
  variant: ButtonVariant;
  size: ButtonSize;
  target: ButtonTarget;
  showIcon: boolean;
};

type CitiesBlockData = {
  texts?: {
    title?: string;
    subtitle?: string;
    text?: string;
  };
  media?: MediaValue;
  cities?: number[];
  buttons?: CitiesButton[];
};

const defaultButton: CitiesButton = {
  text: 'Всі міста',
  link: '/kategoria-produktu/kwiaciarnie-w-polsce/',
  variant: 'green',
  size: 'lg',
  target: '_self',
  showIcon: false,
};

const buttonVariants: ButtonVariant[] = ['green', 'purple', 'white'];
const buttonSizes: ButtonSize[] = ['lg', 'md', 'sm'];
const buttonTargets: ButtonTarget[] = ['_self', '_blank'];

const isButtonVariant = (value: unknown): value is ButtonVariant =>
  typeof value === 'string' && buttonVariants.includes(value as ButtonVariant);

const isButtonSize = (value: unknown): value is ButtonSize =>
  typeof value === 'string' && buttonSizes.includes(value as ButtonSize);

const isButtonTarget = (value: unknown): value is ButtonTarget =>
  typeof value === 'string' && buttonTargets.includes(value as ButtonTarget);

const normalizeButton = (value: unknown): CitiesButton => {
  const source =
    typeof value === 'object' && value !== null
      ? (value as Record<string, unknown>)
      : {};

  return {
    text: typeof source.text === 'string' ? source.text : defaultButton.text,
    link: typeof source.link === 'string' ? source.link : defaultButton.link,
    variant: isButtonVariant(source.variant)
      ? source.variant
      : defaultButton.variant,
    size: isButtonSize(source.size) ? source.size : defaultButton.size,
    target: isButtonTarget(source.target)
      ? source.target
      : defaultButton.target,
    showIcon:
      typeof source.showIcon === 'boolean'
        ? source.showIcon
        : defaultButton.showIcon,
  };
};

const citiesAttributes = {
  texts: {
    type: 'object',
    default: {} as CitiesBlockData['texts'],
  },
  media: {
    type: 'object',
    default: defaultMediaValue,
  },
  cities: {
    type: 'array',
    default: [] as number[],
  },
  buttons: {
    type: 'array',
    default: [defaultButton] as CitiesButton[],
  }
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof citiesAttributes>({
  slug: 'cities',
  title: 'Cities',
  icon: 'location-alt',
  description: 'Cities section with category selection and CTA button.',
  attributes: citiesAttributes,
  includeBaseAttributes: false,
  save: () => {
    return null;
  },
  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    const buttons = Array.isArray(attributes.buttons)
      ? attributes.buttons.map(normalizeButton)
      : [];
    const normalizedButtons =
      buttons.length > 0
        ? buttons
        : [defaultButton];

    const updateButton = (index: number, nextButton: CitiesButton) => {
      setAttributes({
        buttons: normalizedButtons.map((button, buttonIndex) =>
          buttonIndex === index ? nextButton : button,
        ),
      });
    };

    const removeButton = (index: number) => {
      setAttributes({
        buttons: normalizedButtons.filter(
          (_, buttonIndex) => buttonIndex !== index,
        ),
      });
    };

    const addButton = () => {
      setAttributes({
        buttons: [...normalizedButtons, { ...defaultButton }],
      });
    };

    return (
      <>
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
            title="Cities"
            initialOpen={false}
            items={attributes.cities}
            onChange={(cities) => setAttributes({ cities })}
            entityKind="taxonomy"
            entityName="product_cat"
            query={{
              per_page: -1,
              orderby: 'name',
              order: 'asc',
            }}
            selectLabel="Category"
            placeholder="Select category"
            help="Selected product categories are rendered as city links."
            addButtonLabel="Add city category"
            renderItemLabel={(item, index) =>
              item ? `City #${item}` : `City ${index + 1}`
            }
            getOptionLabel={(record) =>
              record.slug
                ? `${record.name ?? ''} (${record.slug})`
                : String(record.name ?? '')
            }
          />

          <PanelBody title="Buttons" initialOpen={false}>
            {normalizedButtons.length === 0 ? (
              <Notice status="info" isDismissible={false}>
                No buttons added yet.
              </Notice>
            ) : null}

            {normalizedButtons.map((button, index) => (
              <div
                key={`cities-button-${index}`}
                style={{
                  marginBottom: '16px',
                  paddingBottom: '16px',
                  borderBottom: '1px solid #ddd',
                }}
              >
                <div
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: '8px',
                    marginBottom: '12px',
                  }}
                >
                  <strong>{button.text || `Button ${index + 1}`}</strong>
                  <Button
                    variant="tertiary"
                    isDestructive
                    onClick={() => removeButton(index)}
                  >
                    Remove
                  </Button>
                </div>

                <TextControl
                  label="Text"
                  value={button.text}
                  onChange={(text: string) =>
                    updateButton(index, { ...button, text })
                  }
                />

                <TextControl
                  label="Link"
                  value={button.link}
                  onChange={(link: string) =>
                    updateButton(index, { ...button, link })
                  }
                />

                <SelectControl
                  label="Variant"
                  value={button.variant}
                  options={[
                    { label: 'Green', value: 'green' },
                    { label: 'Purple', value: 'purple' },
                    { label: 'White', value: 'white' },
                  ]}
                  onChange={(variant) =>
                    updateButton(index, { ...button, variant })
                  }
                />

                <SelectControl
                  label="Size"
                  value={button.size}
                  options={[
                    { label: 'Large', value: 'lg' },
                    { label: 'Medium', value: 'md' },
                    { label: 'Small', value: 'sm' },
                  ]}
                  onChange={(size) =>
                    updateButton(index, { ...button, size })
                  }
                />

                <SelectControl
                  label="Target"
                  value={button.target}
                  options={[
                    { label: 'Self', value: '_self' },
                    { label: 'Blank', value: '_blank' },
                  ]}
                  onChange={(target) =>
                    updateButton(index, { ...button, target })
                  }
                />

                <ToggleControl
                  label="Show icon"
                  checked={Boolean(button.showIcon)}
                  onChange={(showIcon: boolean) =>
                    updateButton(index, { ...button, showIcon })
                  }
                />
              </div>
            ))}

            <Button variant="secondary" onClick={addButton}>
              Add button
            </Button>
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <ServerSideRender
            block="sage/cities"
            httpMethod="POST"
            attributes={{
              texts: attributes.texts,
              media: attributes.media,
              cities: attributes.cities,
              buttons: normalizedButtons,
            }}
          />
        </div>
      </>
    );
  },
});
