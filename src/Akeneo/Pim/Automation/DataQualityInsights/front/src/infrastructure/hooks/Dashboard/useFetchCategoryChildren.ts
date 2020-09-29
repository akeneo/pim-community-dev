import {useEffect, useState} from 'react';
import fetchCategoryChildren from "../../fetcher/Dashboard/fetchCategoryChildren";

const useFetchCategoryChildren = (locale: string, categoryId: string, isOpened: boolean) => {

  const [categoryChildren, setCategoryChildren] = useState({} as any);

  useEffect(() => {

    if (!isOpened) {
      return;
    }

    (async () => {
      let data = await fetchCategoryChildren(locale, categoryId);
      setCategoryChildren(data);
    })();
  }, [categoryId, isOpened]);

  return categoryChildren;
};

export default useFetchCategoryChildren;
