import {useCallback, useContext, useState} from 'react';
import {Locale} from '../../models';
import {LocalesIndexContext, LocalesIndexState} from '../../components/providers';
import {fetchActivatedLocales} from '../../infrastructure/fetchers';

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

  return {
    locales,
    isPending,
    load,
  };
};

export {useLocalesIndexState, useInitialLocalesIndexState, LocalesIndexState};
