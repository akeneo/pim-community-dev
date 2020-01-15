import { useState, useEffect } from 'react';
import {fetchDqiDashboardData} from "../fetcher";

const useFetchDqiDashboardData = (channel: string, locale: string, periodicity: string, familyCode: string | null) => {

  const [dqiDashboardData, setDqiDashboardData] = useState([] as any);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, periodicity, familyCode);
      setDqiDashboardData(data);
    })();
    }, [channel, locale, periodicity, familyCode]);


  return dqiDashboardData;
};

export default useFetchDqiDashboardData;
