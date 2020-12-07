import {useCallback, useState} from 'react';
import {Locale} from '../../models';
import {fetchActivatedLocales} from '../../infrastructure/fetchers';

export type ActivatedLocalesState = {
  locales: Locale[];
  isPending: boolean;
  load: () => Promise<void>;
};

const useActivatedLocales = (): ActivatedLocalesState => {
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

export {useActivatedLocales};
