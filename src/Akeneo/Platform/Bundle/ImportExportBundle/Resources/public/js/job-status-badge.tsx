import React, {useEffect, useState} from "react";
import {JobStatus, useJobExecutionProgress} from "./useJobExecutionStatus";
import {Badge} from "akeneo-design-system";


// const badgeLevel = (jobStatus: Job): string => {
//   if (jobStatus.hasError) {
//     return 'danger';
//   }
//   if (jobStatus.hasWarning) {
//     return 'secondary';
//   }
//
//   return 'primary';
// }

type JobExecutionStatusProps = {
  jobExecutionId: string;
  status: JobStatus;
};

const JobExecutionStatus = ({jobExecutionId, status}: JobExecutionStatusProps) => {
  const jobExecutionProgress = useJobExecutionProgress(jobExecutionId, status)
  // const level = badgeLevel(jobExecutionProgress.status);

  return <Badge>{jobExecutionProgress.status === 'STARTED' ? `${jobExecutionProgress.status} ${jobExecutionProgress.currentStep}/${jobExecutionProgress.totalSteps}` : jobExecutionProgress.status}</Badge>
}

export = JobExecutionStatus;
