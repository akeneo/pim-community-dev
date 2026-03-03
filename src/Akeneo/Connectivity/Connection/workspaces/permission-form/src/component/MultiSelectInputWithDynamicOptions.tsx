import $ from 'jquery';
import React, {useCallback, useEffect, useMemo, useRef} from 'react';
import {getColor} from 'akeneo-design-system';
import {createGlobalStyle} from 'styled-components';

export type QueryParamsBuilder<Context, Params> = (search: string, page: number, context: Context | null) => Params;

type Select2Option = {
    id: string;
    text: string;
};

type Select2Configuration = {
    multiple: boolean;
    closeOnSelect: boolean;
    ajax: {
        url: string;
        dataType: string;
        results: (data: any) => {results: Select2Option[]; more: boolean; context?: any};
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
    url: string;
    fetchByIdentifiers: (identifiers: string[]) => Promise<Select2Option[]>;
    processResults: (data: any) => {
        results: Select2Option[];
        more: boolean;
        context?: any;
    };
    buildQueryParams?: QueryParamsBuilder<any, any>;
    disabled: boolean;
    value: string[];
    onChange?: (value: string[]) => void;
    onAdd?: (value: string) => void;
    onRemove?: (value: string) => void;
};

const GlobalStyle = createGlobalStyle`
    .select2-container.select2-container-disabled .select2-choices {
        background-position: calc(100% - 10px) 15px;
    }
    li.select2-search-choice {
        color: ${getColor('grey', 140)} !important;
        border: 1px ${getColor('grey', 80)} solid !important;
        background-color: ${getColor('grey', 20)} !important;
        align-items: center !important;
        padding-left: 26px !important;
    }
    .select2-search-choice-close {
        opacity: 0.4 !important;
        background-size: 16px !important;
        left: 6px !important;
    }
    .select2-container-disabled .select2-choices li.select2-search-choice {
        padding-left: 8px !important;
    }
`;

export const MultiSelectInputWithDynamicOptions = ({
    url,
    fetchByIdentifiers,
    processResults,
    buildQueryParams,
    disabled,
    value,
    onChange,
    onAdd,
    onRemove,
}: Props) => {
    const ref = useRef<HTMLInputElement>(null);

    /* istanbul ignore next */
    const handleInitSelection = useCallback(
        (element, callback) => {
            const val = element.val().trim();

            if (val.length === 0) {
                callback([]);
                return;
            }

            const identifiers = val.split(',');

            fetchByIdentifiers(identifiers).then(results => {
                callback(results);
            });
        },
        [fetchByIdentifiers, processResults]
    );

    const configuration: Select2Configuration = useMemo(
        () => ({
            multiple: true,
            closeOnSelect: true,
            ajax: {
                url: url,
                cache: true,
                quietMillis: 250,
                dataType: 'json',
                data: buildQueryParams || undefined,
                results: processResults,
            },
            initSelection: handleInitSelection,
        }),
        [url, processResults]
    );

    useEffect(() => {
        /* istanbul ignore next */
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
            /* istanbul ignore else */
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
        /* istanbul ignore next */
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        const select2Value = $select.select2('val') || [];

        /* istanbul ignore else */
        if (value.toString() !== select2Value.toString()) {
            $select.select2('val', value);
        }
    }, [value]);

    useEffect(() => {
        /* istanbul ignore next */
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        $select.select2('enable', !disabled);
    }, [disabled]);

    return (
        <>
            <GlobalStyle />
            <input type='hidden' ref={ref} data-testid='select2' />
        </>
    );
};
