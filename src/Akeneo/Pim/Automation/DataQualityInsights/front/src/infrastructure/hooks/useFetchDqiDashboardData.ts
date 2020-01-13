import { useState, useEffect } from 'react';
import {fetchDqiDashboardData} from "../fetcher";

const useFetchDqiDashboardData = (channel: string, locale: string, periodicity: string) => {

  const [dqiDashboardData, setDqiDashboardData] = useState([] as any);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, periodicity);
      setDqiDashboardData(data);
    })();
    }, [channel, locale, periodicity]);


  return dqiDashboardData;
}

export default useFetchDqiDashboardData;
