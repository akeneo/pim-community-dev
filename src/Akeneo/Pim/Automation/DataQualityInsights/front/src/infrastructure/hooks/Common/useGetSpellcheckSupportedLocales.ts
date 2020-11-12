import {useEffect, useState} from 'react';
import {fetchSpellcheckSupportedLocales} from '../../fetcher';

const useGetSpellcheckSupportedLocales = () => {
  const [supportedLocales, setSupportedLocales] = useState<string[] | null>(null);

  useEffect(() => {
    (async () => {
      const data = await fetchSpellcheckSupportedLocales();
      setSupportedLocales(data);
    })();
  }, []);

  return supportedLocales;
};

export {useGetSpellcheckSupportedLocales};
