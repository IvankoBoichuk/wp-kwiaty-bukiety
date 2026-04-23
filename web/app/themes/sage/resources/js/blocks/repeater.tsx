import { Button, ComboboxControl, Notice, PanelBody } from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import { createElement, useState } from '@wordpress/element';

type EntityRecord = {
    id?: number | string;
    name?: string;
    slug?: string;
    sku?: string;
    title?: {
        rendered?: string;
    } | string;
};

type EntityOption = {
    label: string;
    value: string;
};

type RepeaterFieldProps = {
    title: string;
    items?: number[];
    onChange: (items: number[]) => void;
    entityKind: string;
    entityName: string;
    query?: Record<string, boolean | number | string>;
    addButtonLabel?: string;
    initialOpen?: boolean;
    selectLabel?: string;
    placeholder?: string;
    emptyItemValue?: number;
    help?: string;
    renderItemLabel?: (item: number, index: number) => string;
    getOptionLabel?: (record: EntityRecord) => string;
};

const defaultOptionLabel = (record: EntityRecord): string => {
    const title = typeof record.title === 'string'
        ? record.title
        : record.title?.rendered ?? record.name ?? '';
    const suffix = record.sku ? ` [${record.sku}]` : record.slug ? ` (${record.slug})` : '';

    return `${title}${suffix}`.trim() || `#${record.id ?? ''}`;
};

const RepeaterField = ({
    title,
    items = [],
    onChange,
    entityKind,
    entityName,
    query = {},
    addButtonLabel = 'Add item',
    initialOpen = false,
    selectLabel = 'Select item',
    placeholder = 'Select by name',
    emptyItemValue = 0,
    help,
    renderItemLabel,
    getOptionLabel = defaultOptionLabel,
}: RepeaterFieldProps) => {
    const list = Array.isArray(items) ? items : [];
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);
    const [dropIndex, setDropIndex] = useState<number | null>(null);
    const { records, isResolving } = useEntityRecords(entityKind as 'postType', entityName, query);

    const options: EntityOption[] = Array.isArray(records)
        ? records.map((record) => {
            const entityRecord = record as EntityRecord;

            return {
                label: getOptionLabel(entityRecord),
                value: String(entityRecord.id ?? ''),
            };
        })
        : [];

    const updateItem = (index: number, nextItem: number) => {
        onChange(list.map((item, itemIndex) => itemIndex === index ? nextItem : item));
    };

    const removeItem = (index: number) => {
        onChange(list.filter((_, itemIndex) => itemIndex !== index));
    };

    const moveItem = (fromIndex: number, toIndex: number) => {
        if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0 || fromIndex >= list.length || toIndex >= list.length) {
            return;
        }

        const nextItems = [...list];
        const [movedItem] = nextItems.splice(fromIndex, 1);

        if (movedItem === undefined) {
            return;
        }

        nextItems.splice(toIndex, 0, movedItem);
        onChange(nextItems);
    };

    const handleDrop = (targetIndex: number) => {
        if (draggedIndex === null) {
            return;
        }

        moveItem(draggedIndex, targetIndex);
        setDraggedIndex(null);
        setDropIndex(null);
    };

    return <PanelBody title={title} initialOpen={initialOpen}>
        {help ? <p style={{ marginTop: 0, color: '#50575e' }}>{help}</p> : null}

        {list.length === 0 ? <Notice status="info" isDismissible={false}>
            No items selected yet.
        </Notice> : null}

        {list.map((item, index) => <div
            key={`${title}-${index}`}
            draggable
            onDragStart={() => {
                setDraggedIndex(index);
                setDropIndex(index);
            }}
            onDragOver={(event: { preventDefault: () => void }) => {
                event.preventDefault();

                if (dropIndex !== index) {
                    setDropIndex(index);
                }
            }}
            onDrop={(event: { preventDefault: () => void }) => {
                event.preventDefault();
                handleDrop(index);
            }}
            onDragEnd={() => {
                setDraggedIndex(null);
                setDropIndex(null);
            }}
            style={{
                marginBottom: '16px',
                padding: '8px',
                paddingBottom: '16px',
                borderBottom: '1px solid #ddd',
                borderRadius: '8px',
                opacity: draggedIndex === index ? 0.55 : 1,
                background: dropIndex === index ? '#f6f7f7' : 'transparent',
            }}
        >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '8px', marginBottom: '12px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <span
                        aria-hidden="true"
                        style={{
                            cursor: 'grab',
                            userSelect: 'none',
                            color: '#50575e',
                            fontSize: '16px',
                            lineHeight: 1,
                        }}
                    >
                        ⋮⋮
                    </span>
                    <strong>{renderItemLabel ? renderItemLabel(item, index) : `${title} ${index + 1}`}</strong>
                </div>

                <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
                    <Button
                        variant="tertiary"
                        isDestructive
                        onClick={() => removeItem(index)}
                    >
                        Remove
                    </Button>
                </div>
            </div>

            <ComboboxControl
                label={selectLabel}
                value={item ? String(item) : ''}
                options={options}
                isLoading={Boolean(isResolving)}
                placeholder={placeholder}
                help={isResolving ? 'Loading options...' : undefined}
                onChange={(nextValue: string | null | undefined) => updateItem(index, !nextValue ? emptyItemValue : Number.parseInt(nextValue, 10) || emptyItemValue)}
                __next40pxDefaultSize
            />

            {dropIndex === index && draggedIndex !== null && draggedIndex !== index ? <div
                style={{
                    marginTop: '8px',
                    color: '#50575e',
                    fontSize: '12px',
                }}
            >
                Drop here
            </div> : null}
        </div>)}

        <Button
            variant="secondary"
            onClick={() => onChange([...list, emptyItemValue])}
        >
            {addButtonLabel}
        </Button>
    </PanelBody>;
};

export { RepeaterField };