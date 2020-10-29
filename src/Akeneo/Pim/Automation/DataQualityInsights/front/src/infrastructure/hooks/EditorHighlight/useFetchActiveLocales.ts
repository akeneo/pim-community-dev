import {useEffect, useState} from 'react';
import fetchActiveLocales from '../../fetcher/ProductEditForm/fetchActiveLocales';
import {useMountedState} from '../Common/useMountedState';

const useFetchActiveLocales = () => {
  const [activeLocales, setActiveLocales] = useState<any[]>([] as any);
  const {isMounted} = useMountedState();

  useEffect(() => {
    (async () => {
      let data = await fetchActiveLocales();

      if (isMounted()) {
        setActiveLocales(data);
      }
    })();
  }, []);

  return activeLocales;
};

export default useFetchActiveLocales;
