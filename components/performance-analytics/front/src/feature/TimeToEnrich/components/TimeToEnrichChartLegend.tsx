import React from 'react';
import {TimeToEnrichFilters} from '../models';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BigPill} from '../../Common/components/BigPill';

type TimeToEnrichChartLegendProps = {
  filters: TimeToEnrichFilters;
};

const FilterHighlight = styled.span`
  font-weight: bold;
`;

const TimeToEnrichChartLegend = ({filters}: TimeToEnrichChartLegendProps) => {
  const translate = useTranslate();

  const handleChannelLegend = () => {
    if (filters.channels.length === 0 || filters.channels.includes('<all_channels>')) {
      return translate('akeneo.performance_analytics.legend.all_channels');
    }
    return translate(
      'akeneo.performance_analytics.legend.channels',
      {channelsCount: filters.channels.length},
      filters.channels.length
    );
  };

  const handleLocaleLegend = () => {
    if (filters.locales.length === 0 || filters.locales.includes('<all_locales>')) {
      return translate('akeneo.performance_analytics.legend.all_locales');
    }
    return translate(
      'akeneo.performance_analytics.legend.locales',
      {localesCount: filters.locales.length},
      filters.locales.length
    );
  };

  return (
    <>
      <FilterHighlight>
        <BigPill /> {translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + filters.metric)}
      </FilterHighlight>
      , {translate('akeneo.performance_analytics.legend.for')}{' '}
      <FilterHighlight>
        {translate('akeneo.performance_analytics.control_panel.select_input.aggregations.' + filters.aggregation)}
      </FilterHighlight>
      , {translate('akeneo.performance_analytics.legend.across')}{' '}
      <FilterHighlight>{handleChannelLegend()}</FilterHighlight> &{' '}
      <FilterHighlight>{handleLocaleLegend()}</FilterHighlight>,{' '}
      {translate('akeneo.performance_analytics.legend.over_the')}{' '}
      <FilterHighlight>
        {translate('akeneo.performance_analytics.control_panel.select_input.periods.' + filters.period)}
      </FilterHighlight>
      .
    </>
  );
};

export {TimeToEnrichChartLegend};
