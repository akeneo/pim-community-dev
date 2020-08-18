import {useEffect, useState} from 'react';
import fetchWidgetFamilies from "../../fetcher/Dashboard/fetchWidgetFamilies";

const useFetchWidgetFamilies = (channel: string, locale: string, familyCodes: string[]) => {

  const [widgetFamilies, setWidgetFamilies] = useState({});

  useEffect(() => {
    if (familyCodes.length === 0) {
      setWidgetFamilies({});
    } else {
      (async () => {
        let data = await fetchWidgetFamilies(channel, locale, familyCodes);
        setWidgetFamilies(data);
      })();
    }
  }, [channel, locale, familyCodes]);

  return widgetFamilies;
};

export default useFetchWidgetFamilies;
