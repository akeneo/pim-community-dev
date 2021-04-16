import React, { FC } from 'react';
import { Override } from '../../../shared';
import { InputProps } from '../InputProps';
declare type TagInputProps = Override<Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string[]>>, {
    value: string[];
    onChange: (tags: string[]) => void;
    placeholder?: string;
    invalid?: boolean;
    onSubmit?: () => void;
}>;
declare const TagInput: FC<TagInputProps>;
export { TagInput };
