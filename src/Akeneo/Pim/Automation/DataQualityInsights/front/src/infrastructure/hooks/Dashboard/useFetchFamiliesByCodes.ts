import {useEffect, useState} from 'react';
import Family from "../../../domain/Family.interface";
import fetchFamiliesByCodes from "../../fetcher/Dashboard/fetchFamiliesByCodes";

const useFetchFamiliesByCodes = (widgetFamilies: any) => {

  const [families, setFamilies] = useState<Family[]>([]);

  useEffect(() => {
    if (Object.keys(widgetFamilies).length === 0) {
      return;
    }
    (async () => {
      const familyCodes = Object.keys(widgetFamilies).map((familyCode: string) => familyCode);
      let data = await fetchFamiliesByCodes(familyCodes);
      setFamilies(data);
    })();
  }, [widgetFamilies]);

  return families;
};

export default useFetchFamiliesByCodes;
