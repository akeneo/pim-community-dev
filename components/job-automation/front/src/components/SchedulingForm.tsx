import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {Field, SelectInput, Helper} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  Automation,
  availableFrequencyOptions,
  CronExpression,
  FrequencyOption,
  getCronExpressionFromFrequencyOption,
  getFrequencyOptionFromCronExpression,
} from '../models';
import {
  DailyFrequencyConfigurator,
  FrequencyConfiguratorProps,
  HourlyFrequencyConfigurator,
  WeeklyFrequencyConfigurator,
} from './FrequencyConfigurator';

const Content = styled.div`
  display: flex;
  flex-direction: row;
  gap: 10px;
`;

const frequencyConfigurators: {[frequencyOption: FrequencyOption]: FunctionComponent<FrequencyConfiguratorProps>} = {
  daily: DailyFrequencyConfigurator,
  weekly: WeeklyFrequencyConfigurator,
  every_4_hours: HourlyFrequencyConfigurator,
  every_8_hours: HourlyFrequencyConfigurator,
  every_12_hours: HourlyFrequencyConfigurator,
};

type SchedulingFormProps = {
  automation: Automation;
  validationErrors: ValidationError[];
  onAutomationChange: (automation: Automation) => void;
};

const SchedulingForm = ({automation, validationErrors, onAutomationChange}: SchedulingFormProps) => {
  const translate = useTranslate();

  const handleCronExpressionChange = (cronExpression: CronExpression) =>
    onAutomationChange({...automation, cron_expression: cronExpression});

  const handleFrequencyOptionChange = (frequencyOption: FrequencyOption) =>
    onAutomationChange({
      ...automation,
      cron_expression: getCronExpressionFromFrequencyOption(frequencyOption, automation.cron_expression),
    });

  const frequencyOption = getFrequencyOptionFromCronExpression(automation.cron_expression);
  const FrequencyComponent = frequencyConfigurators[frequencyOption] ?? null;

  if (null === FrequencyComponent) {
    throw new Error(`No frequency configurator found for frequency option "${frequencyOption}"`);
  }

  return (
    <Field label={translate('akeneo.job_automation.scheduling.frequency.title')}>
      <Content>
        <SelectInput
          value={frequencyOption}
          onChange={handleFrequencyOptionChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
          invalid={0 < getErrorsForPath(validationErrors, '').length}
        >
          {availableFrequencyOptions.map(frequencyOption => (
            <SelectInput.Option value={frequencyOption} key={frequencyOption}>
              {translate(`akeneo.job_automation.scheduling.frequency.${frequencyOption}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        <FrequencyComponent
          frequencyOption={frequencyOption}
          cronExpression={automation.cron_expression}
          validationErrors={validationErrors}
          onCronExpressionChange={handleCronExpressionChange}
        />
      </Content>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {SchedulingForm};
