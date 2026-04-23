import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextareaControl, TextControl } from '@wordpress/components';
import { createElement, Fragment } from '@wordpress/element';

export type MediaType = 'img' | 'video-embed' | 'video-modal';

export type MediaOrigin = 'embed' | 'file';

export type MediaAsset = {
    id: number | null;
    url: string;
    alt: string;
    title: string;
    mime: string;
};

export type MediaEmbed = {
    url: string;
    html: string;
};

export type MediaValue = {
    type: MediaType;
    origin: MediaOrigin;
    file: MediaAsset;
    embed: MediaEmbed;
    poster: MediaAsset;
};

type MediaProps = {
    label?: string;
    value?: Partial<MediaValue> | Record<string, unknown>;
    onChange: (value: MediaValue) => void;
    initialOpen?: boolean;
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

const mediaTypes: MediaType[] = ['img', 'video-embed', 'video-modal'];
const mediaOrigins: MediaOrigin[] = ['embed', 'file'];

const emptyMediaAsset = (): MediaAsset => ({
    id: null,
    url: '',
    alt: '',
    title: '',
    mime: '',
});

export const defaultMediaValue: MediaValue = {
    type: 'img',
    origin: 'file',
    file: emptyMediaAsset(),
    embed: {
        url: '',
        html: '',
    },
    poster: emptyMediaAsset(),
};

const isMediaType = (value: unknown): value is MediaType =>
    typeof value === 'string' && mediaTypes.includes(value as MediaType);

const isMediaOrigin = (value: unknown): value is MediaOrigin =>
    typeof value === 'string' && mediaOrigins.includes(value as MediaOrigin);

const normalizeMediaAsset = (value: unknown): MediaAsset => {
    const source = typeof value === 'object' && value !== null ? value as Record<string, unknown> : {};

    return {
        id: typeof source.id === 'number' ? source.id : null,
        url: typeof source.url === 'string' ? source.url : '',
        alt: typeof source.alt === 'string' ? source.alt : '',
        title: typeof source.title === 'string' ? source.title : '',
        mime: typeof source.mime === 'string' ? source.mime : '',
    };
};

const normalizeMediaValue = (value: MediaProps['value']): MediaValue => {
    const source = typeof value === 'object' && value !== null ? value as Record<string, unknown> : {};
    const legacyType = source.type === 'image' ? 'img' : source.type;
    const legacyFile = {
        id: source.attachmentId,
        url: source.url ?? source.src,
        alt: source.alt,
        title: source.title,
        mime: source.mime,
    };
    const embedSource = typeof source.embed === 'object' && source.embed !== null ? source.embed as Record<string, unknown> : {};

    return {
        type: isMediaType(legacyType) ? legacyType : defaultMediaValue.type,
        origin: isMediaOrigin(source.origin) ? source.origin : defaultMediaValue.origin,
        file: normalizeMediaAsset(source.file ?? legacyFile),
        embed: {
            url: typeof embedSource.url === 'string'
                ? embedSource.url
                : typeof source.url === 'string'
                    ? source.url
                    : '',
            html: typeof embedSource.html === 'string'
                ? embedSource.html
                : typeof source.html === 'string'
                    ? source.html
                    : '',
        },
        poster: normalizeMediaAsset(source.poster),
    };
};

const applySelectedMedia = (current: MediaAsset, media: WpMediaSelection | WpMediaSelection[] | null): MediaAsset => {
    const selectedMedia = Array.isArray(media) ? media[0] : media;

    if (!selectedMedia) {
        return current;
    }

    return {
        id: typeof selectedMedia.id === 'number' ? selectedMedia.id : null,
        url: selectedMedia.url || '',
        alt: selectedMedia.alt || current.alt || '',
        title: selectedMedia.title || current.title || '',
        mime: selectedMedia.mime || selectedMedia.subtype || selectedMedia.type || '',
    };
};

const MediaLibraryField = ({
    label,
    buttonLabel,
    value,
    allowedTypes,
    onChange,
}: {
    label: string;
    buttonLabel: string;
    value: MediaAsset;
    allowedTypes: string[];
    onChange: (value: MediaAsset) => void;
}) => {
    return <div style={{ marginTop: '12px' }}>
        <p style={{ marginBottom: '8px', fontWeight: 500 }}>{label}</p>

        {value.url ? <div style={{ marginBottom: '12px' }}>
            {allowedTypes.includes('image') ? <img
                src={value.url}
                alt={value.alt || value.title || ''}
                style={{ display: 'block', width: '100%', maxHeight: '220px', objectFit: 'cover', borderRadius: '8px' }}
            /> : <video
                src={value.url}
                controls
                muted
                playsInline
                style={{ display: 'block', width: '100%', maxHeight: '220px', borderRadius: '8px' }}
            />}
        </div> : <Notice status="info" isDismissible={false}>
            No file selected yet.
        </Notice>}

        <div style={{ display: 'flex', gap: '8px', marginTop: '12px', marginBottom: '12px', flexWrap: 'wrap' }}>
            <MediaUploadCheck>
                <MediaUpload
                    onSelect={(media) => onChange(applySelectedMedia(value, media as WpMediaSelection | WpMediaSelection[] | null))}
                    allowedTypes={allowedTypes}
                    render={({ open }: { open: () => void }) => <Button variant="secondary" onClick={open}>
                        {buttonLabel}
                    </Button>}
                />
            </MediaUploadCheck>

            {value.url ? <Button
                variant="tertiary"
                isDestructive
                onClick={() => onChange(emptyMediaAsset())}
            >
                Remove
            </Button> : null}
        </div>

        <TextControl
            label="URL"
            value={value.url}
            onChange={(url: string) => onChange({ ...value, url })}
        />
    </div>;
};

const Media = ({ label = 'Media', value, onChange, initialOpen = false }: MediaProps) => {
    const media = normalizeMediaValue(value);
    const isVideo = media.type !== 'img';
    const fileAllowedTypes = media.type === 'img' ? ['image'] : ['video'];

    return <PanelBody title={label} initialOpen={initialOpen}>
        <SelectControl
            label="Type"
            value={media.type}
            options={[
                { label: 'Image', value: 'img' },
                { label: 'Video Embed', value: 'video-embed' },
                { label: 'Video Modal', value: 'video-modal' },
            ]}
            onChange={(type: string) => onChange({
                ...media,
                type: isMediaType(type) ? type : defaultMediaValue.type,
            })}
        />

        <SelectControl
            label="Origin"
            value={media.origin}
            options={[
                { label: 'Embed', value: 'embed' },
                { label: 'File', value: 'file' },
            ]}
            onChange={(origin: string) => onChange({
                ...media,
                origin: isMediaOrigin(origin) ? origin : defaultMediaValue.origin,
            })}
        />

        {media.origin === 'file' ? <MediaLibraryField
            label={isVideo ? 'Source file' : 'Image file'}
            buttonLabel={isVideo ? 'Select video' : 'Select image'}
            value={media.file}
            allowedTypes={fileAllowedTypes}
            onChange={(file) => onChange({ ...media, file })}
        /> : <Fragment>
            <TextControl
                label={isVideo ? 'Embed URL' : 'Image URL'}
                help={isVideo ? 'Paste a YouTube, Vimeo, iframe URL, or direct media URL.' : 'Paste an external image URL.'}
                value={media.embed.url}
                onChange={(url: string) => onChange({
                    ...media,
                    embed: {
                        ...media.embed,
                        url,
                    },
                })}
            />

            {isVideo ? <TextareaControl
                label="Embed HTML"
                help="Optional. Use when you need a raw iframe/embed snippet instead of just a URL."
                rows={4}
                value={media.embed.html}
                onChange={(html: string) => onChange({
                    ...media,
                    embed: {
                        ...media.embed,
                        html,
                    },
                })}
            /> : null}

            {media.embed.url && media.type === 'img' ? <div style={{ marginTop: '12px' }}>
                <img
                    src={media.embed.url}
                    alt={media.file.alt}
                    style={{ display: 'block', width: '100%', maxHeight: '220px', objectFit: 'cover', borderRadius: '8px' }}
                />
            </div> : null}

            {media.embed.url && isVideo ? <Notice status="info" isDismissible={false}>
                Preview for embedded video is not rendered in the sidebar. The saved URL or embed HTML is kept in block attributes.
            </Notice> : null}
        </Fragment>}

        {media.type === 'img' ? <TextControl
            label="Alt"
            value={media.file.alt}
            onChange={(alt: string) => onChange({
                ...media,
                file: {
                    ...media.file,
                    alt,
                },
            })}
        /> : <TextControl
            label="Title"
            value={media.file.title}
            onChange={(title: string) => onChange({
                ...media,
                file: {
                    ...media.file,
                    title,
                },
            })}
        />}

        {isVideo ? <MediaLibraryField
            label="Poster"
            buttonLabel="Select poster"
            value={media.poster}
            allowedTypes={['image']}
            onChange={(poster) => onChange({ ...media, poster })}
        /> : null}
    </PanelBody>;
};

export { Media };