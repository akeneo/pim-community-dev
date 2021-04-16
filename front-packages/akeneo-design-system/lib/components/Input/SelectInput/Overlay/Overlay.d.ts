import { ReactNode } from 'react';
import { VerticalPosition } from '../../../../hooks';
declare type OverlayProps = {
    verticalPosition?: VerticalPosition;
    onClose: () => void;
    children: ReactNode;
};
declare const Overlay: ({ verticalPosition, onClose, children }: OverlayProps) => JSX.Element;
export { Overlay };
