import React from 'react';
import {TimeToEnrichFilters} from '../models';
import {SectionTitle, Button} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BigPill} from '../../Common/components/BigPill';
import {BigDottedPill} from '../../Common/components/BigDottedPill';

type TimeToEnrichChartLegendProps = {
  filters: TimeToEnrichFilters;
  onControlPanelClick: () => void;
  isControlPanelOpen: boolean;
};

const FilterHighlight = styled.span`
  font-weight: bold;
`;

const TimeToEnrichChartLegend = ({filters, onControlPanelClick, isControlPanelOpen}: TimeToEnrichChartLegendProps) => {
  const translate = useTranslate();

  return (
    <SectionTitle>
      <SectionTitle.Title level="secondary">
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
      </SectionTitle.Title>
      <SectionTitle.Spacer />
      <Button ghost={true} size={'small'} level={'secondary'} onClick={onControlPanelClick}>
        {!isControlPanelOpen && <>{translate('akeneo.performance_analytics.control_panel.open_control_panel')}</>}
        {isControlPanelOpen && <>{translate('akeneo.performance_analytics.control_panel.close_control_panel')}</>}
      </Button>
    </SectionTitle>
  );
};

export {TimeToEnrichChartLegend};
