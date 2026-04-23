import { normalizeIconObject, registerBlockType } from "@wordpress/blocks";
import type { BlockAttribute, BlockType } from "@wordpress/blocks";
import { PanelBody, TextareaControl, TextControl } from "@wordpress/components";
import { createElement } from '@wordpress/element';
import { Media, defaultMediaValue } from './media';
import { RepeaterField } from './repeater';
export type { MediaAsset, MediaEmbed, MediaOrigin, MediaType, MediaValue } from './media';

type ContentProps = {
  data?: Record<string, unknown>;
  setAttributes: (value: any) => void;
  attributePath?: string[];
  children?: React.ReactNode;
};

type SageAttributeSchema = Record<string, BlockAttribute>;

type BlockAttributeValue<T extends BlockAttribute> =
  T extends { default: infer TDefault } ? TDefault
  : T extends { type: 'string' } ? string
  : T extends { type: 'boolean' } ? boolean
  : T extends { type: 'number' | 'integer' } ? number
  : T extends { type: 'array' } ? unknown[]
  : T extends { type: 'object' } ? Record<string, unknown>
  : unknown;

type InferAttributeValues<TSchema extends SageAttributeSchema> = {
  [TKey in keyof TSchema]: BlockAttributeValue<TSchema[TKey]>;
};

const baseAttributes = {
  texts: {
    type: 'object',
    default: {
      title: '',
      subtitle: '',
      text: '',
    },
  },
  media: {
    type: 'object',
    default: defaultMediaValue,
  },
} satisfies SageAttributeSchema;

type MergeAttributes<TSchema extends SageAttributeSchema> = Omit<typeof baseAttributes, keyof TSchema> & TSchema;

type SageBlockAttributes<TSchema extends SageAttributeSchema> = InferAttributeValues<MergeAttributes<TSchema>>;

type SageBlockEditProps<TSchema extends SageAttributeSchema> = {
  attributes: SageBlockAttributes<TSchema>;
  setAttributes: (value: Partial<SageBlockAttributes<TSchema>>) => void;
};

type SageBlockSaveProps<TSchema extends SageAttributeSchema> = {
  attributes: SageBlockAttributes<TSchema>;
};

type RegisterSageBlockArgs<TSchema extends SageAttributeSchema> =
  Partial<Omit<BlockType, 'attributes' | 'edit' | 'save' | 'icon'>> & {
    slug: string;
    icon?: string;
    attributes?: TSchema;
    includeBaseAttributes?: boolean;
    edit?: (props: SageBlockEditProps<TSchema>) => JSX.Element | null;
    save?: (props: SageBlockSaveProps<TSchema>) => JSX.Element | null;
  };

type LinesFieldProps = {
  label: string;
  help?: string;
  value?: string[];
  onChange: (value: string[]) => void;
}

const registerSageBlock = <TSchema extends SageAttributeSchema = Record<never, never>>({
  slug,
  title,
  icon,
  description,
  variations = [],
  edit,
  save,
  attributes = {} as TSchema,
  includeBaseAttributes = true,
}: RegisterSageBlockArgs<TSchema>) => {
  registerBlockType(`sage/${slug}`, {
    title,
    icon: normalizeIconObject(icon),
    description,
    variations,
    apiVersion: 3,
    category: "kwiaty-bukiety",
    attributes: {
      ...(includeBaseAttributes ? baseAttributes : {}),
      ...attributes,
    },
    supports: {
      html: false,
      anchor: true,
      customClassName: true,
    },
    edit: edit as BlockType['edit'],
    save: save as BlockType['save'],
  });
};

const buildNestedAttributeUpdate = (path: string[], value: Record<string, unknown>): Record<string, unknown> => {
  if (path.length === 0) {
    return value;
  }

  const [head, ...tail] = path;

  return {
    [head]: tail.length > 0 ? buildNestedAttributeUpdate(tail, value) : value,
  };
};

const Content = ({ data = {}, setAttributes, attributePath = ['texts'], children }: ContentProps) => {
  const titleValue = typeof data.title === 'string' ? data.title : '';
  const subtitleValue = typeof data.subtitle === 'string' ? data.subtitle : '';
  const textValue = typeof data.text === 'string' ? data.text : '';

  return <PanelBody title="Content" initialOpen>
    {/* Title */}
    <TextareaControl
      label="Title"
      value={titleValue}
      onChange={(value: string) => {
        setAttributes(buildNestedAttributeUpdate(attributePath, {
          ...data,
          title: value,
        }));
      }}
    />

    {/* Subtitle */}
    <TextControl
      label="Subtitle"
      value={subtitleValue}
      onChange={(value: string) => {
        setAttributes(buildNestedAttributeUpdate(attributePath, {
          ...data,
          subtitle: value,
        }));
      }}
    />

    {/* Text */}
    <div className="">
      <TextareaControl
        label="Text"
        help="This field stores HTML. Rich text selection toolbar is not available in the block sidebar."
        value={textValue}
        onChange={(value: string) => {
          setAttributes(buildNestedAttributeUpdate(attributePath, {
            ...data,
            text: value,
          }));
        }}
      />
      <p style={{ marginTop: '12px', color: '#50575e' }}>
        Text formatting is available directly in the block preview.
      </p>
    </div>

    {/* Other blocks */}
    {children}
  </PanelBody>
}

const LinesField = ({ label, help, value, onChange }: LinesFieldProps) => {
  return <TextareaControl
    label={label}
    help={help}
    rows={4}
    value={Array.isArray(value) ? value.join('\n') : ''}
    onChange={(nextValue: string) => {
      onChange(
        nextValue
          .split(/\r?\n/)
          .map((line) => line.trim())
          .filter(Boolean),
      );
    }}
  />
}

export {
  Content,
  registerSageBlock,
  LinesField,
  Media,
  RepeaterField,
  defaultMediaValue,
};