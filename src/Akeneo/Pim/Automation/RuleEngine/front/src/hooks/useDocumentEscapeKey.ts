import {useEffect} from 'react';

const useDocumentEscapeKey = (onKeyPress: () => void): void => {
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) =>
      'Escape' === event.code && onKeyPress();

    document.addEventListener('keydown', handleKeyDown, true);
    return () => document.removeEventListener('keydown', handleKeyDown, true);
  }, [onKeyPress]);
};

export {useDocumentEscapeKey};
