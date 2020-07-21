import {useEffect, useState} from 'react';
import fetchFamilies from "../../fetcher/Dashboard/fetchFamilies";
import Family from "../../../domain/Family.interface";

const useFetchFamilies = (isActive: boolean, uiLocale: string) => {

  const [families, setFamilies] = useState<Family[]>([]);

  useEffect(() => {
    if (!isActive) {
      return;
    }
    (async () => {

      let data = await fetchFamilies();
      data = Object.values(data).sort((family1: any, family2: any) => {
        const family1Label = family1.labels[uiLocale] ? family1.labels[uiLocale] : "[" + family1.code + "]";
        const family2Label = family2.labels[uiLocale] ? family2.labels[uiLocale] : "[" + family2.code + "]";

        return family1Label.localeCompare(family2Label, uiLocale.replace('_', '-'), {sensitivity: 'base'});
      });
      setFamilies(data);
    })();
  }, [isActive]);

  return families;
};

export default useFetchFamilies;
