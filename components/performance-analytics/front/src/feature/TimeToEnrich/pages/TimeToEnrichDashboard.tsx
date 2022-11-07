import React, {FC, useEffect, useState} from 'react';
import {AddingValueIllustration, Information} from 'akeneo-design-system';
import {TimeToEnrichChartLegend, TimeToEnrichControlPanel, TimeToEnrichHistoricalChart} from '../components';
import {getEndDate, getStartDate, TimeToEnrich, TimeToEnrichFilters} from '../models';
import {AkeneoSpinner, defaultFilters, useFetchers} from '../../Common';
import styled from 'styled-components';

const Container = styled.div<{isControlPanelOpen: boolean}>`
  margin-right: ${({isControlPanelOpen}) => (isControlPanelOpen ? '350px' : '0px')};
`;

const TimeToEnrichDashboard: FC = () => {
  const fetcher = useFetchers();
  const [isControlPanelOpen, setIsControlPanelOpen] = useState<boolean>(false);
  const [referenceTimeToEnrichList, setReferenceTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const [comparisonTimeToEnrichList, setComparisonTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const [filters, setFilters] = useState<TimeToEnrichFilters>(defaultFilters);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchData = async (startDate: string, endDate: string, periodType: string): Promise<TimeToEnrich[]> => {
      return await fetcher.timeToEnrich.fetchHistoricalTimeToEnrich(startDate, endDate, periodType);
    };

    const promise1 = fetchData(getStartDate(filters), getEndDate(filters), 'week');
    promise1.then(async timeToEnrichList => setReferenceTimeToEnrichList(timeToEnrichList));
    const promise2 = fetchData(getStartDate(filters), getEndDate(filters), 'week');
    promise2.then(async timeToEnrichList => setComparisonTimeToEnrichList(timeToEnrichList));
    Promise.all([promise1, promise2]).then(() => setIsLoading(false));

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [JSON.stringify(filters)]);

  const handleControlPanelClick = () => setIsControlPanelOpen(!isControlPanelOpen);

  const handleFiltersChange = (newFilters: TimeToEnrichFilters) => {
    setIsControlPanelOpen(false);
    setFilters(newFilters);
  };

  return (
    <Container isControlPanelOpen={isControlPanelOpen}>
      <Information illustration={<AddingValueIllustration />} title={<>Insights</>}>
        <p>
          Your average time-to-activate is <b>4 days</b> and decreased from 4% over the last 12 weeks.
          <br />
          This is <b>26% better</b> than the <b>standards of your industry</b>.<br />
          The family “Xylophones” is the most at risk. <b>Focus on this family</b>
        </p>
      </Information>

      {filters && (
        <TimeToEnrichChartLegend
          filters={filters}
          isControlPanelOpen={isControlPanelOpen}
          onControlPanelClick={handleControlPanelClick}
        />
      )}
      {(!referenceTimeToEnrichList || !comparisonTimeToEnrichList || isLoading) && <AkeneoSpinner />}
      {referenceTimeToEnrichList && comparisonTimeToEnrichList && (
        <TimeToEnrichHistoricalChart
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
        />
      )}

      <TimeToEnrichControlPanel isOpen={isControlPanelOpen} onFiltersChange={handleFiltersChange} filters={filters} />
    </Container>
  );
};

export {TimeToEnrichDashboard};
