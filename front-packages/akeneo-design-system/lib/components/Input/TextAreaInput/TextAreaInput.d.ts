import React from 'react';
import { InputProps } from '../InputProps';
import { Override } from '../../../shared';
import { EditorProps } from './RichTextEditor';
declare type TextAreaInputProps = Override<Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>, ({
    readOnly: true;
} | {
    readOnly?: boolean;
    onChange: (newValue: string) => void;
}) & {
    value: string;
    placeholder?: string;
    invalid?: boolean;
    characterLeftLabel?: string;
    isRichText?: boolean;
    richTextEditorProps?: EditorProps;
}>;
declare const TextAreaInput: React.ForwardRefExoticComponent<TextAreaInputProps & React.RefAttributes<HTMLInputElement>>;
export { TextAreaInput };
export type { TextAreaInputProps };
