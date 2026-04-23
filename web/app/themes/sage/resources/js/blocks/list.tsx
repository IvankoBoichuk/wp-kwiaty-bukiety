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

type ListLayout = 'how-it-works' | 'faq' | 'guarantees';

type ListItem = {
  title?: string;
  text?: string;
  icon?: string;
};

type WpMediaSelection = {
  url?: string;
};

const defaultListItem = (): ListItem => ({
  title: '',
  text: '',
  icon: '',
});

const normalizeListItem = (value: unknown): ListItem => {
  const source =
    typeof value === 'object' && value !== null
      ? (value as Record<string, unknown>)
      : {};

  return {
    title: typeof source.title === 'string' ? source.title : '',
    text: typeof source.text === 'string' ? source.text : '',
    icon: typeof source.icon === 'string' ? source.icon : '',
  };
};

const listAttributes = {
  layout: {
    type: 'string',
    default: 'how-it-works' as ListLayout,
  },
  list: {
    type: 'array',
    default: [] as ListItem[],
  },
} satisfies Record<string, BlockAttribute>;

registerSageBlock<typeof listAttributes>({
  slug: 'list',
  title: 'List',
  icon: 'editor-ul',
  description:
    'Shared list block with How It Works, FAQ, and Guarantees variants.',
  attributes: listAttributes,
  variations: [
    {
      name: 'how-it-works',
      title: 'List: How It Works',
      description: 'Steps list with side image.',
      isDefault: true,
      scope: ['block', 'inserter', 'transform'],
      isActive: ['layout'],
      attributes: {
        layout: 'how-it-works',
      },
    },
    {
      name: 'faq',
      title: 'List: FAQ',
      description: 'Accordion FAQ list.',
      scope: ['block', 'inserter', 'transform'],
      isActive: ['layout'],
      attributes: {
        layout: 'faq',
      },
    },
    {
      name: 'guarantees',
      title: 'List: Guarantees',
      description: 'Guarantees cards list with icon support.',
      scope: ['block', 'inserter', 'transform'],
      isActive: ['layout'],
      attributes: {
        layout: 'guarantees',
      },
    },
  ],
  save: () => {
    return null;
  },
  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    const items = Array.isArray(attributes.list)
      ? attributes.list.map(normalizeListItem)
      : [];
    const isGuarantees = attributes.layout === 'guarantees';

    const updateItem = (index: number, nextItem: ListItem) => {
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
        list: [...items, defaultListItem()],
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
              return (
                <div
                  key={`list-item-${index}`}
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
                      {item.title ? item.title : `Item ${index + 1}`}
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
                    label="Title"
                    value={item.title || ''}
                    onChange={(title: string) =>
                      updateItem(index, { ...item, title })
                    }
                  />

                  <TextControl
                    label="Text"
                    value={item.text || ''}
                    onChange={(text: string) =>
                      updateItem(index, { ...item, text })
                    }
                  />

                  {isGuarantees ? (
                    <div style={{ marginTop: '12px' }}>
                      <p style={{ marginBottom: '8px', fontWeight: 500 }}>
                        Icon image
                      </p>

                      {item.icon ? (
                        <img
                          src={item.icon}
                          alt={item.title || ''}
                          style={{
                            display: 'block',
                            width: '100%',
                            maxHeight: '64px',
                            objectFit: 'contain',
                            borderRadius: '8px',
                            background: '#f6f7f7',
                          }}
                        />
                      ) : (
                        <Notice status="info" isDismissible={false}>
                          No icon selected yet.
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
                                icon: (media as WpMediaSelection)?.url || '',
                              })
                            }
                            allowedTypes={['image']}
                            render={({ open }: { open: () => void }) => (
                              <Button variant="secondary" onClick={open}>
                                Select icon
                              </Button>
                            )}
                          />
                        </MediaUploadCheck>

                        {item.icon ? (
                          <Button
                            variant="tertiary"
                            isDestructive
                            onClick={() =>
                              updateItem(index, { ...item, icon: '' })
                            }
                          >
                            Remove icon
                          </Button>
                        ) : null}
                      </div>

                      <TextControl
                        label="Icon URL"
                        value={item.icon || ''}
                        onChange={(icon: string) =>
                          updateItem(index, { ...item, icon })
                        }
                      />
                    </div>
                  ) : null}
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
            block="sage/list"
            httpMethod="POST"
            attributes={{
              layout: attributes.layout,
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
