import { useState, useEffect } from 'react';
import {fetchDqiDashboardData} from "../fetcher";

const useFetchDqiDashboardData = (channel: string, locale: string) => {

  const [dqiDashboardData, setDqiDashboardData] = useState([] as any);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale);
      setDqiDashboardData(data);
      console.log(data);
    })();
    }, [channel, locale]);


  return dqiDashboardData;
}

export default useFetchDqiDashboardData;
