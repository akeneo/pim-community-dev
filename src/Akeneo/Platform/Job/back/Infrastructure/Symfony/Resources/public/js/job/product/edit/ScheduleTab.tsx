import React, {useState, useEffect} from 'react';
import {TextField, useRoute, useMediator} from '@akeneo-pim-community/shared';

type ScheduleTabProps = {
  jobInstanceCode: string;
}

const ScheduleTab = ({jobInstanceCode}: ScheduleTabProps) => {
  const getRoute = useRoute('akeneo_job_get_job_instance_schedule', {job_instance_code: jobInstanceCode});
  const saveRoute = useRoute('akeneo_job_save_job_instance_schedule');
  const mediator = useMediator();
  const [cronExpression, setCronExpression] = useState('');

  useEffect(() => {
    const fetchSchedule = async () => {
      const response = await fetch(getRoute);
      const data = await response.json();

      setCronExpression(data?.cron_expression ?? '');
    };

    const saveSchedule = async () => {
      const data = {
        job_instance_code: jobInstanceCode,
        cron_expression: cronExpression,
      };

      await fetch(saveRoute, {
        method: 'POST',
        body: JSON.stringify(data),
      });
    };

    mediator.on('pim_enrich:form:entity:pre_save', saveSchedule);

    void fetchSchedule();
  }, [getRoute, saveRoute]);

  return (
    <TextField
      label="CRON Expression"
      value={cronExpression}
      onChange={setCronExpression}
    />
  );
}

export type {ScheduleTabProps};
export {ScheduleTab};
