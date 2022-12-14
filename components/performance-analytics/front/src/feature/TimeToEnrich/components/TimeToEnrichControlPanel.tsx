import React, {useState} from 'react';
import {
  getColor,
  AkeneoThemedProps,
  SectionTitle,
  Collapse,
  IconButton,
  CloseIcon,
  pimTheme,
  Helper,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {
  Aggregations,
  Metrics,
  PredefinedComparisons,
  PredefinedPeriods,
  MultiChannelInput,
  MultiLocaleInput,
  SelectAggregationInput,
  SelectPeriodInput,
  SelectComparisonInput,
  SelectMetricInput,
  Metric,
} from '../../Common';
import {TimeToEnrichFilters} from '../models';

const Container = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  width: ${({isOpen}) => (isOpen ? '350px' : '0px')};
  transition: width 0.3s linear;
  position: absolute;
  top: 0;
  right: 0;
  background-color: #ffffff;
  border: 1px solid ${getColor('grey', 20)};
  height: 100%;
  overflow: hidden;
  padding: ${({isOpen}) => (isOpen ? '20px' : '0px')};
  z-index: 800;
`;

const ControlPanelSectionTitle = styled(SectionTitle)`
  border-bottom-color: ${getColor('purple', 100)};
  margin-bottom: 10px;
`;

const ControlPanelTitle = styled(SectionTitle.Title)`
  color: ${getColor('purple', 100)};
`;

const Field = styled.div`
  margin-bottom: 5px;
`;

const InlineFieldLabel = styled.div`
  margin-bottom: 5px;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 5px;
`;

const InlineLabel = styled.div`
  overflow: hidden;
  max-width: 80px;
  flex: 1;
`;

const InlineField = styled.div`
  overflow: hidden;
  flex: 1;
`;

const HelperHighlight = styled.span`
  font-weight: bold;
`;

type Props = {
  isOpen: boolean;
  onFiltersChange: (filters: TimeToEnrichFilters) => void;
  onIsControlPanelOpenChange: (isOpen: boolean) => void;
  filters: TimeToEnrichFilters;
  activateComparison?: boolean;
};

const TimeToEnrichControlPanel = ({
  isOpen,
  onFiltersChange,
  onIsControlPanelOpenChange,
  filters,
  activateComparison = false,
}: Props) => {
  const [isCompareFilterCollapsed, toggleCompareFilterCollapse] = useState<boolean>(true);
  const [isPeriodFilterCollapsed, togglePeriodFilterCollapse] = useState<boolean>(true);
  const [isComparisonFilterCollapsed, toggleComparisonFilterCollapse] = useState<boolean>(true);
  const [isFilteredOnCollapsed, toggleFilteredOnFilterCollapse] = useState<boolean>(true);
  const translate = useTranslate();

  const handleIsControlPanelOpenChange = () => {
    onIsControlPanelOpenChange(!isOpen);
  };

  return (
    <Container isOpen={isOpen}>
      <ControlPanelSectionTitle>
        <ControlPanelTitle>{isOpen && translate('akeneo.performance_analytics.control_panel.title')}</ControlPanelTitle>
        <SectionTitle.Spacer />
        <IconButton
          ghost
          icon={<CloseIcon color={pimTheme.color.purple100} />}
          onClick={handleIsControlPanelOpenChange}
          data-testid="close-control-panel"
          title={translate('akeneo.performance_analytics.control_panel.close_control_panel')}
        />
      </ControlPanelSectionTitle>

      {filters.metric === Metric.TIME_TO_ENRICH && (
        <Helper level="info">
          <HelperHighlight>
            {translate('akeneo.performance_analytics.control_panel.select_input.metrics.time_to_enrich')}{' '}
          </HelperHighlight>
          {translate('akeneo.performance_analytics.control_panel.time_to_enrich_helper')}
        </Helper>
      )}

      <Collapse
        collapseButtonLabel={isCompareFilterCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={<>{translate('akeneo.performance_analytics.control_panel.metric_title')}</>}
        onCollapse={toggleCompareFilterCollapse}
        isOpen={isCompareFilterCollapsed}
      >
        <Field>
          <SelectMetricInput filters={Metrics} value={filters.metric} onChange={() => {}} />
        </Field>
        <InlineFieldLabel>
          <InlineLabel>{translate('akeneo.performance_analytics.control_panel.grouped_by')}</InlineLabel>
          <InlineField>
            <SelectAggregationInput
              filters={Aggregations}
              value={filters.aggregation}
              onChange={(aggregation: string) => {
                let updatedAggregation: {};
                updatedAggregation = {aggregation: aggregation};
                onFiltersChange({...filters, ...updatedAggregation});
              }}
            />
          </InlineField>
        </InlineFieldLabel>
      </Collapse>

      <Collapse
        collapseButtonLabel={isFilteredOnCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={<>{translate('akeneo.performance_analytics.control_panel.filters_title')}</>}
        onCollapse={toggleFilteredOnFilterCollapse}
        isOpen={isFilteredOnCollapsed}
      >
        <Field>
          <MultiChannelInput
            onChange={(channels: string[]) => {
              let updatedChannels: {};
              updatedChannels = {channels: channels};
              onFiltersChange({...filters, ...updatedChannels});
            }}
          />
        </Field>
        <Field>
          <MultiLocaleInput
            onChange={(locales: string[]) => {
              let updatedLocales: {};
              updatedLocales = {locales: locales};
              onFiltersChange({...filters, ...updatedLocales});
            }}
          />
        </Field>
      </Collapse>

      <Collapse
        collapseButtonLabel={isPeriodFilterCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={<>{translate('akeneo.performance_analytics.control_panel.period_title')}</>}
        onCollapse={togglePeriodFilterCollapse}
        isOpen={isPeriodFilterCollapsed}
      >
        <Field>
          <SelectPeriodInput
            filters={PredefinedPeriods}
            value={filters.period}
            onChange={(period: string) => {
              let updatedPeriod: {};
              updatedPeriod = {period: period};
              onFiltersChange({...filters, ...updatedPeriod});
            }}
          />
        </Field>
      </Collapse>

      {activateComparison && (
        <Collapse
          collapseButtonLabel={
            isComparisonFilterCollapsed ? translate('pim_common.close') : translate('pim_common.open')
          }
          label={<>{translate('akeneo.performance_analytics.control_panel.compare_title')}</>}
          onCollapse={toggleComparisonFilterCollapse}
          isOpen={isComparisonFilterCollapsed}
        >
          <Field>
            <SelectComparisonInput
              filters={PredefinedComparisons}
              value={filters.comparison}
              onChange={(comparison: string) => {
                let updatedComparison: {};
                updatedComparison = {comparison: comparison};
                onFiltersChange({...filters, ...updatedComparison});
              }}
            />
          </Field>
        </Collapse>
      )}
    </Container>
  );
};

export {TimeToEnrichControlPanel};
