import {useEffect, useState} from 'react';

const isDocumentVisible = () => 'hidden' !== document.visibilityState;

const useDocumentVisibility = (): boolean => {
  const [isVisible, setVisible] = useState<boolean>(isDocumentVisible());

  const handleVisibilityChange = () => setVisible(isDocumentVisible());

  useEffect(() => {
    window.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      window.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, []);

  return isVisible;
};

export {useDocumentVisibility};
