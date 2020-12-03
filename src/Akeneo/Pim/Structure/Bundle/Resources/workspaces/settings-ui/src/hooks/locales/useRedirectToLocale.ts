import {useCallback} from 'react';
import {Locale} from '../../models';

const useRedirectToLocale = () => {
  return useCallback((locale: Locale) => {
    console.warn(`edition of the "${locale.code}" locale`);
  }, []);
};

export {useRedirectToLocale};
