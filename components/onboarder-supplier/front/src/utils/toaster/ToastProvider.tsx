import React, { ReactElement, ReactNode, useCallback, useState } from 'react';
import { ToastContext } from './ToastContext';
import { Toaster, Toast } from './Toaster';
import { IconProps, MessageBarLevel } from 'akeneo-design-system';

const TOAST_DURATION_MS = 5000;

type Props = {
  children: React.ReactNode;
};

export const ToastProvider = ({ children }: Props) => {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const addToast = useCallback(
    (
      title: string,
      level: MessageBarLevel,
      icon?: ReactElement<IconProps>,
      message?: ReactNode
    ) => {
      setToasts((toasts) => [...toasts, { title, level, icon, message }]);
      setTimeout(
        () => setToasts((toasts) => toasts.slice(1)),
        TOAST_DURATION_MS
      );
    },
    []
  );

  return (
    <ToastContext.Provider value={addToast}>
      {children}
      <Toaster toasts={toasts} />
    </ToastContext.Provider>
  );
};
