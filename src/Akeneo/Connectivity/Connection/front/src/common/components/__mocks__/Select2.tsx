import React, {ChangeEvent} from 'react';
import {Select2 as BaseSelect2, Select2Configuration} from '../Select2';

export {Select2Configuration};

type Props = {
    configuration: Select2Configuration;
    value?: string;
    onChange: (value?: string) => void;
};

export const Select2: typeof BaseSelect2 = ({configuration, value, onChange}: Props) => {
    const defaultValue = value || configuration.data[0].id;
    const handleChange = (event: ChangeEvent<HTMLSelectElement>) => onChange(event.target.value);

    return (
        <select defaultValue={defaultValue} onChange={handleChange}>
            {Object.values(configuration.data).map(({id, text}) => (
                <option key={id} value={id}>
                    {text}
                </option>
            ))}
        </select>
    );
};
