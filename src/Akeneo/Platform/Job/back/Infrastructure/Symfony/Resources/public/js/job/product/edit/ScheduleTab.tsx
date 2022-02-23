import React, {useState, useEffect} from 'react';
import {Helper} from 'akeneo-design-system';
import {TextField, useRoute, useMediator, Section} from '@akeneo-pim-community/shared';

type ScheduleTabProps = {
  jobInstanceCode: string;
};

const ScheduleTab = ({jobInstanceCode}: ScheduleTabProps) => {
  const getRoute = useRoute('akeneo_job_get_job_instance_schedule', {job_instance_code: jobInstanceCode});
  const saveRoute = useRoute('akeneo_job_save_job_instance_schedule');
  const mediator = useMediator();
  const [cronExpression, setCronExpression] = useState<string>('');

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

  useEffect(() => {
    const fetchSchedule = async () => {
      const response = await fetch(getRoute);
      const data = await response.json();

      setCronExpression(data?.cron_expression ?? '');
    };

    void fetchSchedule();
  }, [getRoute, saveRoute]);

  useEffect(() => {
    mediator.on('job_schedule:pre_save', saveSchedule);
  }, [cronExpression]);

  return (
    <Section>
      <Helper>
        Logoden biniou degemer mat an penn ar, bed Arzhal ul da nor vrec'h ezhomm, bolz us medisin krouiñ davet
        kreisteiz.
      </Helper>
      <TextField
        placeholder="*/30 * * * *"
        label="CRON Expression"
        value={cronExpression}
        onChange={setCronExpression}
      />
    </Section>
  );
};

export {ScheduleTab};
export type {ScheduleTabProps};
