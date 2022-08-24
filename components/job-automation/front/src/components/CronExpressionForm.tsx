import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {Field, SelectInput, Helper} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  availableFrequencyOptions,
  CronExpression,
  FrequencyOption,
  getCronExpressionFromFrequencyOption,
  getFrequencyOptionFromCronExpression,
  isHourlyFrequency,
} from '../models';
import {
  FrequencyConfiguratorProps,
  TimeFrequencyConfigurator,
  WeeklyFrequencyConfigurator,
} from './FrequencyConfigurator';

const FormField = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const FrequencyFields = styled.div`
  display: flex;
  flex-direction: row;
  gap: 10px;
  max-width: 460px;
`;

const FixedWidthField = styled(Field)`
  min-width: 160px;
  flex-grow: 1;
`;

const frequencyConfigurators: {[frequencyOption: string]: FunctionComponent<FrequencyConfiguratorProps>} = {
  daily: TimeFrequencyConfigurator,
  weekly: WeeklyFrequencyConfigurator,
  every_4_hours: () => null,
  every_8_hours: () => null,
  every_12_hours: () => null,
};

type CronExpressionFormProps = {
  cronExpression: CronExpression;
  validationErrors: ValidationError[];
  onCronExpressionChange: (cronExpression: CronExpression) => void;
};

const CronExpressionForm = ({cronExpression, validationErrors, onCronExpressionChange}: CronExpressionFormProps) => {
  const translate = useTranslate();

  const handleFrequencyOptionChange = (frequencyOption: FrequencyOption) =>
    onCronExpressionChange(getCronExpressionFromFrequencyOption(frequencyOption, cronExpression));

  const frequencyOption = getFrequencyOptionFromCronExpression(cronExpression);
  const FrequencyComponent = frequencyConfigurators[frequencyOption] ?? null;

  if (null === FrequencyComponent) {
    throw new Error(`No frequency configurator found for frequency option "${frequencyOption}"`);
  }

  return (
    <FormField>
      <FrequencyFields>
        <FixedWidthField label={translate('akeneo.job_automation.scheduling.frequency.title')}>
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
        </FixedWidthField>
        <FrequencyComponent
          cronExpression={cronExpression}
          validationErrors={validationErrors}
          onCronExpressionChange={onCronExpressionChange}
        />
      </FrequencyFields>
      {isHourlyFrequency(frequencyOption) ? (
        <Helper inline={true} level="info">
          {translate('akeneo.job_automation.scheduling.frequency.hourly_helper')}
        </Helper>
      ) : (
        <Helper inline={true} level="info">
          {translate('akeneo.job_automation.scheduling.frequency.helper')}
        </Helper>
      )}
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </FormField>
  );
};

export {CronExpressionForm};
