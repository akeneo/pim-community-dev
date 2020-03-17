import {useCallback, useContext, useEffect, useState} from 'react';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {Locale} from 'akeneomeasure/model/locale';
import {baseFetcher} from 'akeneomeasure/shared/fetcher/base-fetcher';

const useUiLocales = (): Locale[] | null => {
  const [locales, setLocales] = useState<Locale[] | null>(null);
  const route = useContext(RouterContext).generate('pim_localization_locale_index');

  const fetchLocales = useCallback(async () => setLocales(await baseFetcher(route)), [route, setLocales]);

  useEffect(() => {
    (async () => fetchLocales())();
  }, []);

  return locales;
};

export {useUiLocales};
