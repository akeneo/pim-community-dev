import React, {ReactNode, useContext} from 'react';
import {useBooleanState} from 'akeneo-design-system';

type ReloadPreviewValue = [boolean, () => void];

const ReloadPreviewContext = React.createContext<ReloadPreviewValue | null>(null);

type ReloadPreviewProviderProps = {
  initialValue?: boolean;
  children: ReactNode;
};

const ReloadPreviewProvider = ({initialValue = false, children}: ReloadPreviewProviderProps) => {
  const [reloadPreview, startReloading, stopReloading] = useBooleanState(initialValue);

  const toggleReloading = () => {
    startReloading();

    setTimeout(() => {
      stopReloading();
    }, 500);
  };

  return (
    <ReloadPreviewContext.Provider value={[reloadPreview, toggleReloading]}>{children}</ReloadPreviewContext.Provider>
  );
};

const useReloadPreview = (): ReloadPreviewValue => {
  const reloadPreviewContext = useContext(ReloadPreviewContext);

  if (!reloadPreviewContext) {
    throw new Error('ReloadPreview context is not properly initialized');
  }

  return reloadPreviewContext;
};

export {useReloadPreview, ReloadPreviewProvider};
