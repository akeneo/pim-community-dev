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
        return family1.labels[uiLocale].localeCompare(family2.labels[uiLocale], uiLocale.replace('_', '-'), {sensitivity: 'base'});
      });
      setFamilies(data);
    })();
  }, [isFilterDisplayed]);

  return families;
};

export default useFetchFamilies;
