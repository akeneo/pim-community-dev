import React from "react";
import {Badge} from "akeneo-design-system";

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';
type BadgeLevel = 'danger' | 'warning' | 'primary';

const badgeLevel = (status: JobStatus, hasError: boolean, hasWarning: boolean): BadgeLevel => {
  if (status === 'FAILED' || hasError) {
    return 'danger';
  }
  if (hasWarning) {
    return 'warning';
  }

  return 'primary';
}

type JobExecutionStatusProps = {
  jobExecutionId: string;
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
}

const JobExecutionStatus = ({status, currentStep, totalSteps, hasWarning, hasError}: JobExecutionStatusProps) => {
  const level = badgeLevel(status, hasError, hasWarning);

  return <Badge level={level}>{jobStatusLabel(status, currentStep, totalSteps)}</Badge>
}

export default JobExecutionStatus;
