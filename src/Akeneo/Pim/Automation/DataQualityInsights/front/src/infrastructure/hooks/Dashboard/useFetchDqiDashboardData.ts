import { useEffect, useState } from 'react';
import { fetchDqiDashboardData } from "../../fetcher";

export type Ranks = {
  [rank: string]: number;
}

export type AxisRates = {
  [date: string]: Ranks;
};

export type Dataset = {
  [axisName: string]: AxisRates;
};

const useFetchDqiDashboardData = (channel: string, locale: string, timePeriod: string, familyCode: string | null, categoryCode: string | null) => {

  const [dqiDashboardData, setDqiDashboardData] = useState<Dataset|null>(null);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, timePeriod, familyCode, categoryCode);
      setDqiDashboardData(data);
    })();
    }, [channel, locale, timePeriod, familyCode, categoryCode]);


  return dqiDashboardData;
};

export default useFetchDqiDashboardData;
