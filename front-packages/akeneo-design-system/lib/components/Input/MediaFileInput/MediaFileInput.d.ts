import React from 'react';
import { Override } from '../../../shared';
import { InputProps } from '../InputProps';
import { FileInfo } from './FileInfo';
declare type MediaFileInputProps = Override<Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<FileInfo | null>>, ({
    readOnly: true;
} | {
    readOnly?: boolean;
    onChange: (newValue: FileInfo | null) => void;
}) & {
    value: FileInfo | null;
    thumbnailUrl: string | null;
    uploader: (file: File, onProgress: (ratio: number) => void) => Promise<FileInfo>;
    placeholder?: string;
    uploadingLabel: string;
    clearTitle: string;
    uploadErrorLabel: string;
    size?: 'default' | 'small';
    invalid?: boolean;
}>;
declare const MediaFileInput: React.ForwardRefExoticComponent<MediaFileInputProps & React.RefAttributes<HTMLInputElement>>;
export { MediaFileInput };
