import $ from 'jquery';
import React, {useCallback, useEffect, useMemo, useRef} from 'react';

type Select2Option = {
    id: string;
    text: string;
}

type Select2Configuration = {
    multiple: boolean;
    closeOnSelect: boolean;
    ajax: {
        url: string;
        dataType: string;
        results: (data: any) => { results: Select2Option[] };
        cache?: boolean;
        quietMillis?: number;
    };
    initSelection: (element: JQuery, callback: (data: Select2Option[]) => void) => void;
};

type Select2Change = {
    val: string[];
    added?: Select2Option;
    removed?: Select2Option;
};

type Props = {
    url: string,
    fetchByIdentifiers: (identifiers: string[]) => Promise<Select2Option[]>;
    processResults: (data: any) => {
        results: Select2Option[];
    };
    disabled: boolean;
    value: string[];
    onChange?: (value: string[]) => void;
    onAdd?: (value: string) => void;
    onRemove?: (value: string) => void;
};

export const MultiSelectInputWithDynamicOptions = (
    {
        url,
        fetchByIdentifiers,
        processResults,
        disabled,
        value,
        onChange,
        onAdd,
        onRemove,
    }: Props,
) => {
    const ref = useRef<HTMLInputElement>(null);
    const handleInitSelection = useCallback((element, callback) => {
        const val = element.val().trim();

        if (val.length === 0) {
            callback([]);
            return;
        }

        const identifiers = val.split(',');

        fetchByIdentifiers(identifiers).then(results => {
            callback(results);
        });
    }, [fetchByIdentifiers, processResults]);

    const configuration: Select2Configuration = useMemo(() => ({
        multiple: true,
        closeOnSelect: true,
        ajax: {
            url: url,
            cache: true,
            quietMillis: 250,
            dataType: 'json',
            results: processResults,
        },
        initSelection: handleInitSelection,
    }), [url, processResults]);

    useEffect(() => {
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        $select.val(value.join(','));
        $select.select2(configuration);
        $select.select2('enable', !disabled);
        $select.on('change', (event: Select2Change) => {
            if (event.added && onAdd) {
                onAdd(event.added.id.toString());
            }
            if (event.removed && onRemove) {
                onRemove(event.removed.id.toString());
            }
            if (onChange) {
                onChange(event.val);
            }
        });

        return () => {
            const $container = $select.select2('container');

            $select.off('change');
            $select.select2('destroy');
            /* istanbul ignore next */
            $container && $container.remove();
        };
    }, [configuration]);

    useEffect(() => {
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        const select2Value = $select.select2('val');

        if (value !== select2Value) {
            $select.select2('val', value);
        }
    }, [value]);

    useEffect(() => {
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        $select.select2('enable', !disabled);
    }, [disabled]);

    return <input type="hidden" ref={ref} data-testid="select2"/>;
};
