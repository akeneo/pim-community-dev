import React from 'react';
import {SelectInput, TextInput} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate} from '@akeneo-pim-community/shared';
import {
  getTimeFromCronExpression,
  getWeekDayFromCronExpression,
  getWeeklyCronExpressionFromTime,
  getWeeklyCronExpressionFromWeekDay,
  weekDays,
} from '../../models';
import {FrequencyConfiguratorProps} from './FrequencyConfiguratorProps';

const WeeklyFrequencyConfigurator = ({
  cronExpression,
  validationErrors,
  onCronExpressionChange,
}: FrequencyConfiguratorProps) => {
  const translate = useTranslate();

  const handleWeekDayChange = (weekDay: string) =>
    onCronExpressionChange(getWeeklyCronExpressionFromWeekDay(weekDay, cronExpression));

  const handleTimeChange = (time: string) =>
    onCronExpressionChange(getWeeklyCronExpressionFromTime(time, cronExpression));

  return (
    <>
      <SelectInput
        value={getWeekDayFromCronExpression(cronExpression)}
        onChange={handleWeekDayChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        clearable={false}
        invalid={0 < getErrorsForPath(validationErrors, '[week_day_number]').length}
      >
        {weekDays.map(weekDay => (
          <SelectInput.Option value={weekDay} key={weekDay}>
            {translate(`akeneo.job_automation.scheduling.frequency.${weekDay}`)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      <TextInput
        type="time"
        invalid={0 < getErrorsForPath(validationErrors, '[time]').length}
        value={getTimeFromCronExpression(cronExpression)}
        onChange={handleTimeChange}
      />
    </>
  );
};

export {WeeklyFrequencyConfigurator};
