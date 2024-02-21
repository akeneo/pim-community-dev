import {useEffect, useRef, useState} from 'react';
import Family from '../../../domain/Family.interface';
import {useRouter} from '@akeneo-pim-community/shared';

const useFetchFamiliesByCodes = (widgetFamilies: any) => {
  const [families, setFamilies] = useState<Family[]>([]);
  const router = useRouter();
  const mountedRef = useRef<boolean>(false);

  useEffect(() => {
    if (Object.keys(widgetFamilies).length === 0) {
      return;
    }
    (async () => {
      const familyCodes = Object.keys(widgetFamilies).map((familyCode: string) => familyCode);
      let response = await fetch(
        router.generate('akeneo_data_quality_insights_find_families', {
          identifiers: familyCodes,
        })
      );
      const data = await response.json();
      if (!mountedRef.current) {
        return;
      }
      setFamilies(data);
    })();
  }, [widgetFamilies]);

  useEffect(() => {
    mountedRef.current = true;

    return () => {
      mountedRef.current = false;
    };
  }, []);

  return families;
};

export default useFetchFamiliesByCodes;
