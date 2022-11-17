import {useEffect} from 'react';
import {Key} from 'akeneo-design-system';

const useValidateFormWithEnter: (confirmCallback: () => void) => void = confirmCallback => {
  return useEffect(() => {
    const listener = (event: KeyboardEvent) => {
      if (event.code === Key.Enter || event.code === Key.NumpadEnter) {
        confirmCallback();
        event.preventDefault();
      }
    };
    document.addEventListener('keydown', listener);

    return () => {
      document.removeEventListener('keydown', listener);
    };
  }, [confirmCallback]);
};

export {useValidateFormWithEnter};
