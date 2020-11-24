import {useEffect, useState} from 'react';
import {fetchDqiDashboardData} from '../../fetcher';
import {ScoreDistributionByDate} from '../../../domain';

const useFetchDqiDashboardData = (
  channel: string,
  locale: string,
  timePeriod: string,
  familyCode: string | null,
  categoryCode: string | null
) => {
  const [dqiDashboardData, setDqiDashboardData] = useState<ScoreDistributionByDate | null>(null);

  useEffect(() => {
    (async () => {
      const data = await fetchDqiDashboardData(channel, locale, timePeriod, familyCode, categoryCode);
      setDqiDashboardData(data);
    })();
  }, [channel, locale, timePeriod, familyCode, categoryCode]);

  return dqiDashboardData;
};

export default useFetchDqiDashboardData;
