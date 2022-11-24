import React, {useState} from 'react';
import {
  getColor,
  AkeneoThemedProps,
  SectionTitle,
  Collapse,
  IconButton,
  CheckIcon,
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
  MultiFamilyInput,
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
};

const TimeToEnrichControlPanel = ({isOpen, onFiltersChange, onIsControlPanelOpenChange, filters}: Props) => {
  const [isCompareFilterCollapsed, toggleCompareFilterCollapse] = useState<boolean>(true);
  const [isPeriodFilterCollapsed, togglePeriodFilterCollapse] = useState<boolean>(true);
  const [isComparisonFilterCollapsed, toggleComparisonFilterCollapse] = useState<boolean>(true);
  const [isFilteredOnCollapsed, toggleFilteredOnFilterCollapse] = useState<boolean>(true);
  const [controlPanelFilters, setControlPanelFilters] = useState<TimeToEnrichFilters>(filters);
  const translate = useTranslate();

  const handleFiltersChange = () => {
    onFiltersChange(controlPanelFilters);
  };

  const handleIsControlPanelOpenChange = () => {
    onIsControlPanelOpenChange(!isOpen);
  };

  const handleControlPanelFilters = (newFilter: object) => {
    setControlPanelFilters(controlPanelFilters => ({
      ...controlPanelFilters,
      ...newFilter,
    }));
  };

  return (
    <Container isOpen={isOpen}>
      <ControlPanelSectionTitle>
        <ControlPanelTitle>{isOpen && translate('akeneo.performance_analytics.control_panel.title')}</ControlPanelTitle>
        <SectionTitle.Spacer />
        {/*<SystemIcon color={pimTheme.color.purple100} size={24} />*/}
        <IconButton
          ghost
          icon={
            <CheckIcon color={pimTheme.color.purple100} onClick={handleFiltersChange} data-testid="validate-filters" />
          }
          onClick={function noRefCheck() {}}
          title={translate('akeneo.performance_analytics.control_panel.close_control_panel')}
        />
        <IconButton
          ghost
          icon={<CloseIcon color={pimTheme.color.purple100} />}
          onClick={handleIsControlPanelOpenChange}
          data-testid="close-control-panel"
          title={translate('akeneo.performance_analytics.control_panel.close_control_panel')}
        />
      </ControlPanelSectionTitle>

      {controlPanelFilters.metric === Metric.TIME_TO_ENRICH && (
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
          <SelectMetricInput filters={Metrics} value={controlPanelFilters.metric} onChange={() => {}} />
        </Field>
        <InlineFieldLabel>
          <InlineLabel>{translate('akeneo.performance_analytics.control_panel.grouped_by')}</InlineLabel>
          <InlineField>
            <SelectAggregationInput
              filters={Aggregations}
              value={controlPanelFilters.aggregation}
              onChange={(aggregation: string) => {
                let updatedAggregation: {};
                updatedAggregation = {aggregation: aggregation};
                handleControlPanelFilters(updatedAggregation);
              }}
            />
          </InlineField>
        </InlineFieldLabel>
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
            value={controlPanelFilters.period}
            onChange={(period: string) => {
              let updatedPeriod: {};
              updatedPeriod = {period: period};
              handleControlPanelFilters(updatedPeriod);
            }}
          />
        </Field>
      </Collapse>

      <Collapse
        collapseButtonLabel={isComparisonFilterCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={<>{translate('akeneo.performance_analytics.control_panel.compare_title')}</>}
        onCollapse={toggleComparisonFilterCollapse}
        isOpen={isComparisonFilterCollapsed}
      >
        <Field>
          <SelectComparisonInput
            filters={PredefinedComparisons}
            value={controlPanelFilters.comparison}
            onChange={(comparison: string) => {
              let updatedComparison: {};
              updatedComparison = {comparison: comparison};
              handleControlPanelFilters(updatedComparison);
            }}
          />
        </Field>
      </Collapse>

      <Collapse
        collapseButtonLabel={isFilteredOnCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={<>{translate('akeneo.performance_analytics.control_panel.filtered_on_title')}</>}
        onCollapse={toggleFilteredOnFilterCollapse}
        isOpen={isFilteredOnCollapsed}
      >
        <Field>
          <MultiFamilyInput
            onChange={(families: string[]) => {
              let updatedFamilies: {};
              updatedFamilies = {families: families};
              handleControlPanelFilters(updatedFamilies);
            }}
          />
        </Field>
        <Field>
          <MultiChannelInput
            onChange={(channels: string[]) => {
              let updatedChannels: {};
              updatedChannels = {channels: channels};
              handleControlPanelFilters(updatedChannels);
            }}
          />
        </Field>
        <Field>
          <MultiLocaleInput
            onChange={(locales: string[]) => {
              let updatedLocales: {};
              updatedLocales = {locales: locales};
              handleControlPanelFilters(updatedLocales);
            }}
          />
        </Field>
      </Collapse>
    </Container>
  );
};

export {TimeToEnrichControlPanel};
