import React, {FC, useState, useEffect} from 'react';
import {Information, AddingValueIllustration} from 'akeneo-design-system';
import {TimeToEnrichHistoricalChart, TimeToEnrichChartLegend} from '../components';
import {TimeToEnrich, TimeToEnrichFilters} from '../models';
import {useFetchers} from '../../Common';

const TimeToEnrichDashboard: FC = () => {
  const fetcher = useFetchers();

  const [referenceTimeToEnrichList, setReferenceTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const [comparisonTimeToEnrichList, setComparisonTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const filters: TimeToEnrichFilters = {
    family: 'all',
    category: 'all',
    channel: 'all',
    locale: 'all',
  };

  useEffect(() => {
    const fetchData = async (
      startDate: string,
      endDate: string,
      periodType: string,
      callback: (result: TimeToEnrich[]) => void
    ) => {
      await fetcher.timeToEnrich
        .fetchHistoricalTimeToEnrich(startDate, endDate, periodType)
        .then(async timeToEnrichList => callback(timeToEnrichList));
    };
    fetchData('2022-07-01', '2022-09-30', 'week', result => setReferenceTimeToEnrichList(result));
    fetchData('2022-07-01', '2022-09-30', 'week', result => setComparisonTimeToEnrichList(result));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <Information illustration={<AddingValueIllustration />} title={<>Insights</>}>
        <p>
          Your average time-to-activate is <b>4 days</b> and decreased from 4% over the last 12 weeks.
          <br />
          This is <b>26% better</b> than the <b>standarts of your industry</b>.<br />
          The family “Xylophones” is the most at risk. <b>Focus on this family</b>
        </p>
      </Information>
      {filters && <TimeToEnrichChartLegend filters={filters} />}
      {referenceTimeToEnrichList && comparisonTimeToEnrichList && (
        <TimeToEnrichHistoricalChart
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
        />
      )}
    </>
  );
};

export {TimeToEnrichDashboard};
