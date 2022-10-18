import React from 'react';
import {TimeToEnrichFilters} from '../models';
import {SectionTitle, Button, PanelOpenIcon, getColor} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

type TimeToEnrichChartLegendProps = {
  filters: TimeToEnrichFilters;
};

const FilterHighlight = styled.span`
  color: ${getColor('purple', 100)};
  font-weight: bold;
`;

const TimeToEnrichChartLegend = ({filters}: TimeToEnrichChartLegendProps) => {
  const translate = useTranslate();

  return (
    <SectionTitle>
      <SectionTitle.Title level="secondary">
        <FilterHighlight>Time-to-enrich</FilterHighlight>, on <FilterHighlight>Family</FilterHighlight>, during{' '}
        <FilterHighlight>Last month</FilterHighlight>, compared to <FilterHighlight>Revenue</FilterHighlight>.
      </SectionTitle.Title>
      <SectionTitle.Spacer />
      <Button ghost={true} size={'small'} level={'secondary'}>
        {translate('akeneo.performance_analytics.graph.control_panel_button')} <PanelOpenIcon />
      </Button>
    </SectionTitle>
  );
};

export {TimeToEnrichChartLegend};
