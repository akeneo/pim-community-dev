import {useEffect, useState} from 'react';
import fetchFamilies from "../fetcher/fetchFamilies";
import Family from "../../domain/Family.interface";

const useFetchFamilies = (isFilterDisplayed: boolean, uiLocale: string) => {

  const [families, setFamilies] = useState<Family[]>([]);

  useEffect(() => {
    if (!isFilterDisplayed) {
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
  }, [isFilterDisplayed]);

  return families;
};

export default useFetchFamilies;
