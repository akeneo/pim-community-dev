import {useEffect, useState} from 'react';
import fetchWidgetCategories from "../../fetcher/Dashboard/fetchWidgetCategories";
import {Category} from "../../../domain";

const useFetchWidgetCategories = (channel: string, locale: string, categories: Category[]) => {

  const [widgetCategories, setWidgetCategories] = useState({});

  useEffect(() => {
    if (categories.length === 0) {
      setWidgetCategories({});
    } else {
      (async () => {
        const categoryCodes = categories.map((category: Category) => category.code);
        const data = await fetchWidgetCategories(channel, locale, categoryCodes);
        setWidgetCategories(data);
      })();
    }
  }, [channel, locale, categories]);

  return widgetCategories;
};

export default useFetchWidgetCategories;
