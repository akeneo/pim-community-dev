import React from 'react';
import {TimeToEnrichFilters} from '../models';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BigPill} from '../../Common/components/BigPill';
import {BigDottedPill} from '../../Common/components/BigDottedPill';

type TimeToEnrichChartLegendProps = {
  filters: TimeToEnrichFilters;
};

const FilterHighlight = styled.span`
  font-weight: bold;
`;

const TimeToEnrichChartLegend = ({filters}: TimeToEnrichChartLegendProps) => {
  const translate = useTranslate();

  return (
    <>
      <FilterHighlight>
        <BigPill /> {translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + filters.metric)}
      </FilterHighlight>
      , on{' '}
      <FilterHighlight>
        {translate('akeneo.performance_analytics.control_panel.select_input.aggregations.' + filters.aggregation)}
      </FilterHighlight>
      , during{' '}
      <FilterHighlight>
        {translate('akeneo.performance_analytics.control_panel.select_input.periods.' + filters.period)}
      </FilterHighlight>
      , compared to <BigDottedPill />{' '}
      <FilterHighlight>
        {translate('akeneo.performance_analytics.control_panel.select_input.comparisons.' + filters.comparison)}
      </FilterHighlight>
      .
    </>
  );
};

export {TimeToEnrichChartLegend};
