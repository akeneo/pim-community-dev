import {useEffect, useState} from 'react';
import fetchActiveLocales from '../fetcher/fetchActiveLocales';

const useFetchActiveLocales = () => {

  const [activeLocales, setActiveLocales] = useState<any[]>([] as any);

  useEffect(() => {
    (async () => {
      let data = await fetchActiveLocales();
      setActiveLocales(data);
    })();
  }, []);

  return activeLocales;
};

export default useFetchActiveLocales;
