import React from "react";
import {JobExecutionProgress, JobStatus, useJobExecutionProgress} from "./useJobExecutionProgress";
import {Badge} from "akeneo-design-system";

type BadgeLevel = 'danger' | 'warning' | 'primary';

const badgeLevel = (jobStatus: JobExecutionProgress): BadgeLevel => {
  if (jobStatus.status === 'FAILED' || jobStatus.hasError) {
    return 'danger';
  }
  if (jobStatus.hasWarning) {
    return 'warning';
  }

  return 'primary';
}

type JobExecutionStatusProps = {
  jobExecutionId: string;
  status: JobStatus;
};

const jobStatusLabel = (jobExecutionProgress: JobExecutionProgress): string => {
  if (jobExecutionProgress.status !== 'STARTING' && jobExecutionProgress.status !== 'STARTED') {
    return jobExecutionProgress.status;
  }

  return `${jobExecutionProgress.status} ${jobExecutionProgress.currentStep}/${jobExecutionProgress.totalSteps}`;
}

const JobExecutionStatus = ({jobExecutionId, status}: JobExecutionStatusProps) => {
  const jobExecutionProgress = useJobExecutionProgress(jobExecutionId, status)
  const level = badgeLevel(jobExecutionProgress);

  return <Badge level={level}>{jobStatusLabel(jobExecutionProgress)}</Badge>
}

export = JobExecutionStatus;
