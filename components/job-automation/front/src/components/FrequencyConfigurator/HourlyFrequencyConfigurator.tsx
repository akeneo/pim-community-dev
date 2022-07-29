import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {getErrorsForPath} from '@akeneo-pim-community/shared';
import {FrequencyConfiguratorProps} from './FrequencyConfiguratorProps';
import {getHourlyCronExpressionFromTime, getTimeFromCronExpression} from '../../models';

const HourlyFrequencyConfigurator = ({
  cronExpression,
  validationErrors,
  onCronExpressionChange,
}: FrequencyConfiguratorProps) => {
  const handleTimeChange = (time: string) =>
    onCronExpressionChange(getHourlyCronExpressionFromTime(time, cronExpression));

  return (
    <TextInput
      type="time"
      invalid={0 < getErrorsForPath(validationErrors, '[time]').length}
      value={getTimeFromCronExpression(cronExpression)}
      onChange={handleTimeChange}
    />
  );
};

export {HourlyFrequencyConfigurator};
