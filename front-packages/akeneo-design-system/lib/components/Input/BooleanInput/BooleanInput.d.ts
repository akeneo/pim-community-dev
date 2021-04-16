import React from 'react';
import { InputProps } from '../InputProps';
import { Override } from '../../../shared';
declare type BooleanInputProps = Override<InputProps<boolean>, ({
    clearable?: true;
    value: boolean | null;
    onChange?: (value: boolean | null) => void;
    clearLabel: string;
} | {
    clearable?: false;
    value: boolean;
    onChange?: (value: boolean) => void;
    clearLabel?: string;
}) & {
    readOnly: boolean;
    yesLabel: string;
    noLabel: string;
}>;
declare const BooleanInput: React.ForwardRefExoticComponent<BooleanInputProps & React.RefAttributes<HTMLDivElement>>;
export { BooleanInput };
