import React from 'react';
import {Badge, Level} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge/src';

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

const badgeLevel = (status: JobStatus, hasError: boolean, hasWarning: boolean): Level => {
  if (status === 'FAILED' || hasError) {
    return 'danger';
  }
  if (hasWarning) {
    return 'warning';
  }

  return 'primary';
};

type JobExecutionStatusProps = {
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  hasWarning: boolean;
  hasError: boolean;
};

const JobExecutionStatus = ({
  status,
  currentStep,
  totalSteps,
  hasWarning,
  hasError,
  ...props
}: JobExecutionStatusProps) => {
  const level = badgeLevel(status, hasError, hasWarning);
  const translate = useTranslate();

  let label = translate(`pim_import_export.job_status.${status}`);
  if (status === 'STARTING' || status === 'STARTED') {
    label = `${label} ${currentStep}/${totalSteps}`;
  }

  return (
    <Badge level={level} {...props}>
      {label}
    </Badge>
  );
};

export default JobExecutionStatus;
export type {JobStatus};
