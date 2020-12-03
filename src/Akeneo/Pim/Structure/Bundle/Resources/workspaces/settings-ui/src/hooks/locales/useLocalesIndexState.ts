import {useCallback, useContext, useState} from 'react';
import {Locale} from '../../models';
import {LocalesIndexContext, LocalesIndexState} from '../../components/providers';
import {fetchActivatedLocales} from '../../infrastructure/fetchers';
import {useRedirectToLocale} from './useRedirectToLocale';

const useLocalesIndexState = (): LocalesIndexState => {
  const context = useContext(LocalesIndexContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'LocalesIndex' context outside Provider");
  }

  return context;
};

const useInitialLocalesIndexState = (): LocalesIndexState => {
  const [locales, setLocales] = useState<Locale[]>([]);
  const [isPending, setIsPending] = useState(true);

  const load = useCallback(async () => {
    setIsPending(true);

    return fetchActivatedLocales().then(collection => {
      setLocales(collection);
      setIsPending(false);
    });
  }, [setLocales, setIsPending]);

  const compare = (source: Locale, target: Locale) => {
    return source.code.localeCompare(target.code);
  };

  const redirect = useRedirectToLocale();

  return {
    locales,
    isPending,
    load,
    compare,
    redirect,
  };
};

export {useLocalesIndexState, useInitialLocalesIndexState, LocalesIndexState};
