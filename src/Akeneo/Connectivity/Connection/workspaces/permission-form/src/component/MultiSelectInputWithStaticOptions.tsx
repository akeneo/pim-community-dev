import $ from 'jquery';
import React, {useEffect, useRef} from 'react';
import {getColor} from 'akeneo-design-system';
import styled from 'styled-components';

export type QueryParamsBuilder<Context, Params> = (search: string, page: number, context: Context | null) => Params;

type Select2Option = {
    id: string;
    text: string;
};

type Select2Configuration = {
    closeOnSelect: boolean;
};

type Select2Change = {
    val: string[];
    added?: Select2Option;
    removed?: Select2Option;
};

type Props = {
    disabled: boolean;
    value: string[];
    onChange?: (value: string[]) => void;
    onAdd?: (value: string) => void;
    onRemove?: (value: string) => void;
    options: Select2Option[];
};

export const MultiSelectInputWithStaticOptions = ({disabled, value, onChange, onAdd, onRemove, options}: Props) => {
    const ref = useRef<HTMLSelectElement>(null);

    useEffect(() => {
        /* istanbul ignore next */
        if (null === ref.current) {
            return;
        }

        const configuration: Select2Configuration = {
            closeOnSelect: true,
        };

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
    }, []);

    useEffect(() => {
        /* istanbul ignore next */
        if (null === ref.current) {
            return;
        }

        const $select = $(ref.current) as any;
        const select2Value = $select.select2('val');

        /* istanbul ignore else */
        if (value !== select2Value) {
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
        <select ref={ref} multiple={true} data-testid='select2'>
            {options &&
                options.map(option => (
                    <option key={option.id} value={option.id}>
                        {option.text}
                    </option>
                ))}
        </select>
    );
};
