import {useEffect, useState} from 'react';

const documentIsVisible = () => 'visible' === document.visibilityState;

const useDocumentVisibility = (): boolean => {
  const [isVisible, setVisible] = useState<boolean>(documentIsVisible());
  const handleVisibilityChange = () => {
    console.log(document.visibilityState);
    setVisible(documentIsVisible());
  };

  useEffect(() => {
    window.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      window.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  });

  return isVisible;
};

export {useDocumentVisibility};
