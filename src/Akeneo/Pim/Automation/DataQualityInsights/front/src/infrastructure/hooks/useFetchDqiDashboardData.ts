import { useState, useEffect } from 'react';
import {fetchDqiDashboardData} from "../fetcher";

const useFetchDqiDashboardData = (channel: string, locale: string, timePeriod: string, familyCode: string | null, categoryCode: string | null) => {

  const [dqiDashboardData, setDqiDashboardData] = useState([] as any);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, timePeriod, familyCode, categoryCode);
      setDqiDashboardData(data);
    })();
    }, [channel, locale, timePeriod, familyCode, categoryCode]);


  return dqiDashboardData;
};

export default useFetchDqiDashboardData;
