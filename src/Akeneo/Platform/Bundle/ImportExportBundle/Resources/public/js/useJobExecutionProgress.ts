const Routing = require('routing');
import {useEffect, useState} from "react";

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

type JobExecutionProgress = {
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  hasWarning: boolean;
  hasError: boolean;
}

const useJobExecutionProgress = (jobExecutionId: string, jobExecutionStatus: JobStatus): JobExecutionProgress => {
  const [jobExecutionProgress, setProgress] = useState<JobExecutionProgress>({
    status: jobExecutionStatus,
    currentStep: 0,
    totalSteps: 0,
    hasWarning: false,
    hasError: false,
  });

  useEffect(() => {
    let isMounted = true;

    const fetchData = async () => {
      const response = await fetch(
        Routing.generate(
          'pim_enrich_job_execution_rest_get',
          {identifier: jobExecutionId}),
        {
          method: 'GET',
          headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
          ]
        }
      );
      const newJobStatus = await response.json();

      if (!isMounted) return;
      let hasWarning = newJobStatus.tracking.steps.some((step: {hasWarning: boolean}) => step.hasWarning);
      let hasError = newJobStatus.tracking.steps.some((step: {hasError: boolean}) => step.hasError);
      setProgress({
        ...jobExecutionProgress,
        currentStep: newJobStatus.tracking.currentStep,
        totalSteps: newJobStatus.tracking.totalSteps,
        hasWarning,
        hasError
      });
    }

    fetchData()

    return () => {
      isMounted = false;
    }
  }, [jobExecutionId]);

  return jobExecutionProgress;
}

export {useJobExecutionProgress};
export type {JobExecutionProgress, JobStatus};
