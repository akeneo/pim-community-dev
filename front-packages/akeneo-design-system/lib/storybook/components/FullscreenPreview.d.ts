import { ReactNode } from 'react';
declare type FullscreenPreviewProps = {
    title: string;
    src: string;
    onClose: () => void;
    children: ReactNode;
};
declare const FullscreenPreview: ({ title, src, onClose, children }: FullscreenPreviewProps) => JSX.Element;
export { FullscreenPreview };
