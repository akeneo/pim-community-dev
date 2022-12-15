import React, {FC, useEffect, useState} from 'react';
import {SectionTitle, Button, PanelCloseIcon} from 'akeneo-design-system';
import {
  TimeToEnrichChartLegend,
  TimeToEnrichControlPanel,
  TimeToEnrichHistoricalChart,
  TimeToEnrichTable,
} from '../components';
import {getEndDate, getPeriodType, getStartDate, TimeToEnrich, TimeToEnrichFilters} from '../models';
import {AkeneoSpinner, defaultFilters, useFetchers} from '../../Common';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div<{isControlPanelOpen: boolean}>`
  margin-right: ${({isControlPanelOpen}) => (isControlPanelOpen ? '350px' : '0px')};
`;

const TimeToEnrichDashboard: FC<{activateComparison?: boolean}> = ({activateComparison = true}) => {
  const fetcher = useFetchers();
  const [isControlPanelOpen, setIsControlPanelOpen] = useState<boolean>(false);
  const [referenceTimeToEnrichList, setReferenceTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const [comparisonTimeToEnrichList, setComparisonTimeToEnrichList] = useState<TimeToEnrich[] | undefined>(undefined);
  const [tableData, setTableData] = useState<TimeToEnrich[] | undefined>(undefined);
  const [filters, setFilters] = useState<TimeToEnrichFilters>(defaultFilters);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const translate = useTranslate();
  let timeToEnrichPromiseList: Promise<TimeToEnrich[]>[] = [];

  useEffect(() => {
    setIsLoading(true);
    const fetchHistoricalTimeToEnrich = async (filters: TimeToEnrichFilters): Promise<TimeToEnrich[]> => {
      return await fetcher.timeToEnrich.fetchHistoricalTimeToEnrich(
        getStartDate(filters),
        getEndDate(filters),
        getPeriodType(filters),
        {
          families: filters.families,
          channels: filters.channels,
          locales: filters.locales,
        }
      );
    };

    const fetchAverageTimeToEnrichByEntity = async (filters: TimeToEnrichFilters): Promise<TimeToEnrich[]> => {
      return await fetcher.timeToEnrich.fetchAverageTimeToEnrichByEntity(
        getStartDate(filters),
        getEndDate(filters),
        filters.aggregation,
        {
          channels: filters.channels,
          locales: filters.locales,
        }
      );
    };

    const fetchHistoricalTimeToEnrichResults = fetchHistoricalTimeToEnrich(filters);
    fetchHistoricalTimeToEnrichResults.then(async timeToEnrichList => setReferenceTimeToEnrichList(timeToEnrichList));
    timeToEnrichPromiseList.push(fetchHistoricalTimeToEnrichResults);

    if (activateComparison) {
      const fetchComparisonTimeToEnrichResults = fetchHistoricalTimeToEnrich(filters);
      fetchComparisonTimeToEnrichResults.then(async timeToEnrichList =>
        setComparisonTimeToEnrichList(timeToEnrichList)
      );
      timeToEnrichPromiseList.push(fetchComparisonTimeToEnrichResults);
    }

    const fetchAverageTimeToEnrichByEntityResults = fetchAverageTimeToEnrichByEntity(filters);
    fetchAverageTimeToEnrichByEntityResults.then(async tableData => setTableData(tableData));
    timeToEnrichPromiseList.push(fetchAverageTimeToEnrichByEntityResults);

    Promise.all(timeToEnrichPromiseList).then(() => setIsLoading(false));

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [JSON.stringify(filters)]);

  const handleFiltersChange = (newFilters: TimeToEnrichFilters) => {
    setFilters(newFilters);
  };

  return (
    <Container isControlPanelOpen={isControlPanelOpen}>
      {isLoading && <AkeneoSpinner />}

      <SectionTitle>
        <SectionTitle.Title level="secondary">
          <TimeToEnrichChartLegend filters={filters} />
        </SectionTitle.Title>

        {!isControlPanelOpen && (
          <>
            <SectionTitle.Spacer />
            <Button
              ghost={true}
              size={'small'}
              level={'secondary'}
              onClick={() => {
                setIsControlPanelOpen(true);
              }}
            >
              <>
                {translate('akeneo.performance_analytics.control_panel.configure')} <PanelCloseIcon size={20} />
              </>
            </Button>
          </>
        )}
      </SectionTitle>
      {referenceTimeToEnrichList && (
        <TimeToEnrichHistoricalChart
          referenceTimeToEnrichList={referenceTimeToEnrichList}
          comparisonTimeToEnrichList={comparisonTimeToEnrichList}
        />
      )}

      {tableData && tableData.length > 0 && <TimeToEnrichTable tableData={tableData} />}

      <TimeToEnrichControlPanel
        isOpen={isControlPanelOpen}
        onFiltersChange={handleFiltersChange}
        onIsControlPanelOpenChange={(value: boolean) => setIsControlPanelOpen(value)}
        filters={filters}
        activateComparison={activateComparison}
      />
    </Container>
  );
};

export {TimeToEnrichDashboard};
