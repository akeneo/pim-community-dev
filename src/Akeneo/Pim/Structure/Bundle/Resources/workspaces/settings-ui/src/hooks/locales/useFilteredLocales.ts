import {useCallback, useEffect, useState} from 'react';
import {Locale} from '../../models';

const useFilteredLocales = (locales: Locale[]) => {
  const [filteredLocales, setFilteredLocales] = useState<Locale[]>([]);

  useEffect(() => {
    setFilteredLocales(locales);
  }, [locales]);

  const search = useCallback(
    (searchValue: string) => {
      setFilteredLocales(
        locales.filter((locale: Locale) => locale.code.toLocaleLowerCase().includes(searchValue.toLowerCase().trim()))
      );
    },
    [locales]
  );

  return {
    filteredLocales,
    search,
  };
};

export {useFilteredLocales};
