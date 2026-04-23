import {
  MediaUpload,
  MediaUploadCheck,
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';
import { Button, Notice, PanelBody, TextControl } from '@wordpress/components';
import type { BlockAttribute } from '@wordpress/blocks';
import { createElement, Fragment } from '@wordpress/element';
import { ServerSideRender } from '@wordpress/server-side-render';
import { Content, Media, registerSageBlock } from './shared';

type NumbersIcon = {
  id: number | null;
  url: string;
  alt: string;
  title: string;
  mime: string;
};

type NumbersItem = {
  text?: string;
  number?: string;
  icon?: NumbersIcon;
};

type WpMediaSelection = {
  id?: number;
  url?: string;
  alt?: string;
  title?: string;
  mime?: string;
  subtype?: string;
  type?: string;
};

const emptyIcon = (): NumbersIcon => ({
  id: null,
  url: '',
  alt: '',
  title: '',
  mime: '',
});

const defaultNumberItem = (): NumbersItem => ({
  text: '',
  number: '',
  icon: emptyIcon(),
});

const normalizeIcon = (value: unknown): NumbersIcon => {
  const source =
    typeof value === 'object' && value !== null
      ? (value as Record<string, unknown>)
      : {};

  return {
    id: typeof source.id === 'number' ? source.id : null,
    url: typeof source.url === 'string' ? source.url : '',
    alt: typeof source.alt === 'string' ? source.alt : '',
    title: typeof source.title === 'string' ? source.title : '',
    mime: typeof source.mime === 'string' ? source.mime : '',
  };
};

const normalizeItem = (value: unknown): NumbersItem => {
  const source =
    typeof value === 'object' && value !== null
      ? (value as Record<string, unknown>)
      : {};

  return {
    text: typeof source.text === 'string' ? source.text : '',
    number: typeof source.number === 'string' ? source.number : '',
    icon: normalizeIcon(source.icon),
  };
};

const applySelectedImage = (
  current: NumbersIcon,
  media: WpMediaSelection | WpMediaSelection[] | null,
): NumbersIcon => {
  const selectedMedia = Array.isArray(media) ? media[0] : media;

  if (!selectedMedia) {
    return current;
  }

  return {
    id: typeof selectedMedia.id === 'number' ? selectedMedia.id : null,
    url: selectedMedia.url || '',
    alt: selectedMedia.alt || current.alt || '',
    title: selectedMedia.title || current.title || '',
    mime:
      selectedMedia.mime || selectedMedia.subtype || selectedMedia.type || '',
  };
};

const numbersAttributes = {
  list: {
    type: 'array',
    default: [] as NumbersItem[],
  },
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof numbersAttributes>({
  slug: 'numbers',
  title: 'Numbers',
  icon: 'editor-ol',
  description: 'Numbers section with image icons, labels, and values.',
  attributes: numbersAttributes,
  save: () => {
    return null;
  },
  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    const items = Array.isArray(attributes.list)
      ? attributes.list.map(normalizeItem)
      : [];

    const updateItem = (index: number, nextItem: NumbersItem) => {
      setAttributes({
        list: items.map((item, itemIndex) =>
          itemIndex === index ? nextItem : item,
        ),
      });
    };

    const removeItem = (index: number) => {
      setAttributes({
        list: items.filter((_, itemIndex) => itemIndex !== index),
      });
    };

    const addItem = () => {
      setAttributes({
        list: [...items, defaultNumberItem()],
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

          <PanelBody title="Items" initialOpen={false}>
            {items.length === 0 ? (
              <Notice status="info" isDismissible={false}>
                No items added yet.
              </Notice>
            ) : null}

            {items.map((item, index) => {
              const icon = normalizeIcon(item.icon);

              return (
                <div
                  key={`numbers-item-${index}`}
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
                    <strong>
                      {item.text ? item.text : `Item ${index + 1}`}
                    </strong>
                    <Button
                      variant="tertiary"
                      isDestructive
                      onClick={() => removeItem(index)}
                    >
                      Remove
                    </Button>
                  </div>

                  <TextControl
                    label="Number"
                    value={item.number || ''}
                    onChange={(number: string) =>
                      updateItem(index, { ...item, number })
                    }
                  />

                  <TextControl
                    label="Text"
                    value={item.text || ''}
                    onChange={(text: string) =>
                      updateItem(index, { ...item, text })
                    }
                  />

                  <div style={{ marginTop: '12px' }}>
                    <p style={{ marginBottom: '8px', fontWeight: 500 }}>
                      Icon image
                    </p>

                    {icon.url ? (
                      <img
                        src={icon.url}
                        alt={icon.alt || icon.title || ''}
                        style={{
                          display: 'block',
                          width: '100%',
                          maxHeight: '160px',
                          objectFit: 'contain',
                          borderRadius: '8px',
                          background: '#f6f7f7',
                        }}
                      />
                    ) : (
                      <Notice status="info" isDismissible={false}>
                        No image selected yet.
                      </Notice>
                    )}

                    <div
                      style={{
                        display: 'flex',
                        gap: '8px',
                        marginTop: '12px',
                        marginBottom: '12px',
                        flexWrap: 'wrap',
                      }}
                    >
                      <MediaUploadCheck>
                        <MediaUpload
                          onSelect={(media) =>
                            updateItem(index, {
                              ...item,
                              icon: applySelectedImage(
                                icon,
                                media as
                                  | WpMediaSelection
                                  | WpMediaSelection[]
                                  | null,
                              ),
                            })
                          }
                          allowedTypes={['image']}
                          render={({ open }: { open: () => void }) => (
                            <Button variant="secondary" onClick={open}>
                              Select image
                            </Button>
                          )}
                        />
                      </MediaUploadCheck>

                      {icon.url ? (
                        <Button
                          variant="tertiary"
                          isDestructive
                          onClick={() =>
                            updateItem(index, { ...item, icon: emptyIcon() })
                          }
                        >
                          Remove image
                        </Button>
                      ) : null}
                    </div>

                    <TextControl
                      label="Image URL"
                      value={icon.url}
                      onChange={(url: string) =>
                        updateItem(index, { ...item, icon: { ...icon, url } })
                      }
                    />

                    <TextControl
                      label="Image alt"
                      value={icon.alt}
                      onChange={(alt: string) =>
                        updateItem(index, { ...item, icon: { ...icon, alt } })
                      }
                    />
                  </div>
                </div>
              );
            })}

            <Button variant="secondary" onClick={addItem}>
              Add item
            </Button>
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <ServerSideRender
            block="sage/numbers"
            httpMethod="POST"
            attributes={{
              texts: attributes.texts,
              media: attributes.media,
              list: items,
            }}
          />
        </div>
      </>
    );
  },
});
