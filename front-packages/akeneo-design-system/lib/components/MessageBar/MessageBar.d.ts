import { ReactNode, ReactElement } from 'react';
import { IconProps } from '../../icons';
declare type MessageBarLevel = 'info' | 'success' | 'warning' | 'error';
declare const AnimateMessageBar: ({ children }: {
    children: ReactElement<MessageBarProps>;
}) => JSX.Element;
declare type FlashMessage = {
    level?: MessageBarLevel;
    title: string;
    icon?: ReactElement<IconProps>;
    children?: ReactNode;
};
declare type MessageBarProps = FlashMessage & {
    dismissTitle: string;
    onClose: () => void;
};
declare const MessageBar: ({ level, title, icon, dismissTitle, onClose, children }: MessageBarProps) => JSX.Element;
export { MessageBar, AnimateMessageBar };
export type { MessageBarLevel, FlashMessage };
