import React, { ReactNode } from 'react';
import { Override } from '../../../shared';
import { InputProps } from '../InputProps';
import { VerticalPosition } from '../../../hooks';
declare type SelectInputProps = Override<Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string | null>>, ({
    readOnly: true;
} | {
    readOnly?: boolean;
    onChange: (newValue: string | null) => void;
}) & ({
    clearable?: true;
    value: string | null;
} | {
    clearable?: false;
    value: string;
}) & {
    placeholder?: string;
    emptyResultLabel: string;
    clearLabel?: string;
    openLabel?: string;
    invalid?: boolean;
    children?: ReactNode;
    verticalPosition?: VerticalPosition;
}>;
declare const SelectInput: {
    ({ id, placeholder, invalid, value, emptyResultLabel, children, onChange, clearable, clearLabel, openLabel, readOnly, verticalPosition, "aria-labelledby": ariaLabelledby, ...rest }: SelectInputProps): JSX.Element;
    Option: import("styled-components").StyledComponent<"span", any, {
        value: string;
    }, never>;
};
export { SelectInput };
