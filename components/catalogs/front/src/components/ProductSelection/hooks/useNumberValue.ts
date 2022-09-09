import {useEffect, useState} from 'react';

type InitialValue = number | string | null;
type NewValue = number | string;
type OnChange = (value: NewValue) => void;
type OnInputChange = (value: string) => void;
type Return = [string, OnInputChange];

const filterValue = (value: string): string =>
    value
        // remove when starting with a dot (not supported)
        .replace(/^\.+/g, '')
        // remove forbidden characters
        .replace(/[^0-9.]+/g, '')
        // keep only one dot
        .split('.')
        .slice(0, 2)
        .join('.');

const numberizeValue = (value: string): NewValue => {
    const decimals = value.includes('.') ? value.split('.')[1].length : 0;
    const number = Number(value);

    // '4.' => 4
    if (value.endsWith('.')) {
        return number;
    }

    // in js, if all decimals are '0', the decimal part is truncated.
    // If the user type '4.0', it's ok to numberize it to 4.
    if (number.toFixed(decimals) === value) {
        return number;
    }

    return value;
};

const initialValueToString = (value: InitialValue): string =>
    typeof value !== 'number' ? filterValue(value || '') : value.toString();

export const useNumberValue = (value: InitialValue, onChange: OnChange): Return => {
    const [inputValue, setInputValue] = useState<string>(initialValueToString(value));

    useEffect(() => {
        setInputValue(initialValueToString(value));
    }, [value, setInputValue]);

    const onInputValueChange: OnInputChange = (value: string) => {
        const filtered = filterValue(value);

        setInputValue(filtered);

        onChange(numberizeValue(filtered));
    };

    return [inputValue, onInputValueChange];
};
