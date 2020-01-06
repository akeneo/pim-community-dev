import $ from 'jquery';
import React, {useEffect, useRef} from 'react';

export type Select2Configuration = {
    placeholder?: string;
    data: Array<{id: string; text: string}>;
    allowClear?: boolean;
    dropdownCssClass?: string;
    formatResult?: (item: {id: string}) => string;
};

type Props = {
    configuration: Select2Configuration;
    value?: string;
    onChange: (value?: string) => void;
};

export const Select2 = ({configuration, value, onChange}: Props) => {
    const ref = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (null === ref.current) {
            return;
        }
        const $select = $(ref.current) as any;
        $select.val(value);
        $select.select2(configuration);
        $select.on('change', ({val}: {val: string}) => onChange(val || undefined));

        return () => {
            $select.off('change');
            $select.select2('destroy');
        };
    }, [ref, configuration, value, onChange]);

    return <input type='hidden' ref={ref} />;
};
