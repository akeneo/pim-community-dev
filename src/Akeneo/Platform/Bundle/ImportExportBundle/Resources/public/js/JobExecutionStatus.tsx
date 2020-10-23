import React from 'react';
import {Badge, Level} from 'akeneo-design-system';

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

const jobStatusLabel = (status: JobStatus, currentStep: number, totalSteps: number): string => {
  if (status !== 'STARTING' && status !== 'STARTED') {
    return status;
  }

  return `${status} ${currentStep}/${totalSteps}`;
};

const JobExecutionStatus = ({status, currentStep, totalSteps, hasWarning, hasError}: JobExecutionStatusProps) => {
  const level = badgeLevel(status, hasError, hasWarning);

  return <Badge level={level}>{jobStatusLabel(status, currentStep, totalSteps)}</Badge>;
};

export default JobExecutionStatus;
export type {JobStatus};
