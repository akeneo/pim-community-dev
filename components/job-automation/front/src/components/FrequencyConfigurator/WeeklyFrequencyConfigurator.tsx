import React from 'react';
import styled from 'styled-components';
import {Field, SelectInput} from 'akeneo-design-system';
import {getErrorsForPath, useTranslate} from '@akeneo-pim-community/shared';
import {getWeekDayFromCronExpression, getWeeklyCronExpressionFromWeekDay, weekDays} from '../../models';
import {FrequencyConfiguratorProps} from './FrequencyConfiguratorProps';
import {TimeFrequencyConfigurator} from './TimeFrequencyConfigurator';

const FixedWidthField = styled(Field)`
  min-width: 160px;
`;

const WeeklyFrequencyConfigurator = ({
  cronExpression,
  validationErrors,
  onCronExpressionChange,
}: FrequencyConfiguratorProps) => {
  const translate = useTranslate();

  const handleWeekDayChange = (weekDay: string) =>
    onCronExpressionChange(getWeeklyCronExpressionFromWeekDay(weekDay, cronExpression));

  return (
    <>
      <FixedWidthField label={translate('akeneo.job_automation.scheduling.frequency.weekday')}>
        <SelectInput
          value={getWeekDayFromCronExpression(cronExpression)}
          onChange={handleWeekDayChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
          invalid={0 < getErrorsForPath(validationErrors, '[week_day]').length}
        >
          {weekDays.map(weekDay => (
            <SelectInput.Option value={weekDay} key={weekDay}>
              {translate(`akeneo.job_automation.scheduling.frequency.${weekDay}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </FixedWidthField>
      <TimeFrequencyConfigurator
        cronExpression={cronExpression}
        validationErrors={validationErrors}
        onCronExpressionChange={onCronExpressionChange}
      />
    </>
  );
};

export {WeeklyFrequencyConfigurator};
