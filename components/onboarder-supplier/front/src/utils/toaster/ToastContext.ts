import { createContext, ReactElement, ReactNode } from 'react';
import { IconProps, MessageBarLevel } from 'akeneo-design-system';

export const ToastContext = createContext<
  | ((
  title: string,
  level: MessageBarLevel,
  icon?: ReactElement<IconProps>,
  message?: ReactNode
) => void)
  | null
  >(null);
