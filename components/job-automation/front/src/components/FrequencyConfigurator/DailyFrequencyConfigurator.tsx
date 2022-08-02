import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {getErrorsForPath} from '@akeneo-pim-community/shared';
import {FrequencyConfiguratorProps} from './FrequencyConfiguratorProps';
import {getDailyCronExpressionFromTime, getTimeFromCronExpression} from '../../models';

const DailyFrequencyConfigurator = ({
  cronExpression,
  validationErrors,
  onCronExpressionChange,
}: FrequencyConfiguratorProps) => {
  const handleTimeChange = (time: string) => onCronExpressionChange(getDailyCronExpressionFromTime(time));

  return (
    <TextInput
      type="time"
      invalid={0 < getErrorsForPath(validationErrors, '[time]').length}
      value={getTimeFromCronExpression(cronExpression)}
      onChange={handleTimeChange}
    />
  );
};

export {DailyFrequencyConfigurator};
