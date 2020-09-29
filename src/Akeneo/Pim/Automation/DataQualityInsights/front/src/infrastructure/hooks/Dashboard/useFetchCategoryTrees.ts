import {useEffect, useState} from 'react';
import fetchCategoryTrees from "../../fetcher/Dashboard/fetchCategoryTrees";

const useFetchCategoryTrees = () => {

  const [categoryTrees, setCategoryTrees] = useState([] as any);

  useEffect(() => {
    (async () => {
      let data = await fetchCategoryTrees();
      setCategoryTrees(data);
    })();
  }, []);

  return categoryTrees;
};

export default useFetchCategoryTrees;
