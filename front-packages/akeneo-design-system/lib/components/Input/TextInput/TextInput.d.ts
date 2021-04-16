import React from 'react';
import { InputProps } from '../InputProps';
import { Override } from '../../../shared';
declare type TextInputProps = Override<Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>, ({
    readOnly: true;
} | {
    readOnly?: boolean;
    onChange: (newValue: string) => void;
}) & {
    value?: string;
    placeholder?: string;
    invalid?: boolean;
    characterLeftLabel?: string;
    onSubmit?: () => void;
}>;
declare const TextInput: React.ForwardRefExoticComponent<TextInputProps & React.RefAttributes<HTMLInputElement>>;
export { TextInput };
export type { TextInputProps };
