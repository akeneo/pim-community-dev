import React, { ReactElement } from 'react';
import { Override } from '../../../shared';
import { InputProps } from '../InputProps';
import { VerticalPosition } from '../../../hooks';
declare type OptionProps = {
    value: string;
    children: string;
} & React.HTMLAttributes<HTMLSpanElement>;
declare type MultiMultiSelectInputProps = Override<Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string[]>>, ({
    readOnly: true;
} | {
    readOnly?: boolean;
    onChange: (newValue: string[]) => void;
}) & {
    value: string[];
    placeholder?: string;
    emptyResultLabel: string;
    openLabel?: string;
    removeLabel: string;
    invalid?: boolean;
    children?: ReactElement<OptionProps>[] | ReactElement<OptionProps>;
    verticalPosition?: VerticalPosition;
    onSubmit?: () => void;
}>;
declare const MultiSelectInput: {
    ({ id, placeholder, invalid, value, emptyResultLabel, children, onChange, removeLabel, onSubmit, openLabel, readOnly, verticalPosition, "aria-labelledby": ariaLabelledby, ...rest }: MultiMultiSelectInputProps): JSX.Element;
    Option: {
        ({ children, ...rest }: OptionProps): JSX.Element;
        displayName: string;
    };
};
export { MultiSelectInput };
