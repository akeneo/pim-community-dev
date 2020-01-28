import { useState, useEffect } from 'react';
import {fetchDqiDashboardData} from "../fetcher";

const useFetchDqiDashboardData = (channel: string, locale: string, periodicity: string, familyCode: string | null, categoryCode: string | null) => {

  const [dqiDashboardData, setDqiDashboardData] = useState([] as any);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, periodicity, familyCode, categoryCode);
      setDqiDashboardData(data);
    })();
    }, [channel, locale, periodicity, familyCode, categoryCode]);


  return dqiDashboardData;
};

export default useFetchDqiDashboardData;
