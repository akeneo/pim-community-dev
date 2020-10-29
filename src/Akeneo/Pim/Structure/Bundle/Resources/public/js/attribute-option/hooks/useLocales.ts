import {useEffect, useState} from 'react';
import baseFetcher from '../fetchers/baseFetcher';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {Locale} from '../model';

const useLocales = () => {
  const [locales, setLocales] = useState<Locale[]>([]);
  const route = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});

  useEffect(() => {
    (async () => {
      setLocales(await baseFetcher(route));
    })();
  }, []);

  return locales;
};

export default useLocales;
