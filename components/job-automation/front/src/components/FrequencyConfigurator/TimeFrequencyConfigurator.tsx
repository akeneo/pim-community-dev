import React from 'react';
import styled from 'styled-components';
import {SelectInput} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate} from '@akeneo-pim-community/shared';
import {
  getHoursFromCronExpression,
  getMinutesFromCronExpression,
  getCronExpressionFromHours,
  getCronExpressionFromMinutes,
} from '../../models';
import {FrequencyConfiguratorProps} from './FrequencyConfiguratorProps';

const NUMBER_OF_HOURS_IN_A_DAY = 24;
const NUMBER_OF_MINUTES_IN_AN_HOUR = 60;

const FixedSelectInput = styled(SelectInput)`
  max-width: 70px;
`;

const TimeFrequencyConfigurator = ({
  cronExpression,
  validationErrors,
  onCronExpressionChange,
}: FrequencyConfiguratorProps) => {
  const translate = useTranslate();

  const handleHoursChange = (hours: string) =>
    onCronExpressionChange(getCronExpressionFromHours(hours, cronExpression));

  const handleMinutesChange = (minutes: string) =>
    onCronExpressionChange(getCronExpressionFromMinutes(minutes, cronExpression));

  return (
    <>
      <FixedSelectInput
        value={getHoursFromCronExpression(cronExpression)}
        onChange={handleHoursChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        clearable={false}
        invalid={0 < getErrorsForPath(validationErrors, '[hours]').length}
      >
        {[...Array(NUMBER_OF_HOURS_IN_A_DAY)].map((_, hour) => {
          const paddedValue = hour.toString().padStart(2, '0');

          return (
            <SelectInput.Option value={paddedValue} key={paddedValue}>
              {paddedValue}
            </SelectInput.Option>
          );
        })}
      </FixedSelectInput>
      <FixedSelectInput
        value={getMinutesFromCronExpression(cronExpression)}
        onChange={handleMinutesChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        clearable={false}
        invalid={0 < getErrorsForPath(validationErrors, '[minutes]').length}
      >
        {[...Array(NUMBER_OF_MINUTES_IN_AN_HOUR / 10)].map((_, minute) => {
          const paddedValue = (minute * 10).toString().padStart(2, '0');

          return (
            <SelectInput.Option value={paddedValue} key={paddedValue}>
              {paddedValue}
            </SelectInput.Option>
          );
        })}
      </FixedSelectInput>
    </>
  );
};

export {TimeFrequencyConfigurator};
