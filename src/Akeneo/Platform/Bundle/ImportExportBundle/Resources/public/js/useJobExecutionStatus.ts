const Routing = require('routing');
import {useEffect, useState} from "react";

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

type JobExecutionProgress = {
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
}

const useJobExecutionProgress = (jobExecutionId: string, jobExecutionStatus: JobStatus): JobExecutionProgress => {
  const [jobExecutionProgress, setProgress] = useState<JobExecutionProgress>({
    status: jobExecutionStatus,
    currentStep: 0,
    totalSteps: 0
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
      setProgress({
        ...jobExecutionProgress,
        currentStep: newJobStatus.currentStep,
        totalSteps: newJobStatus.totalSteps,
      });
    }

    if ('STARTING' === jobExecutionStatus
      || 'STARTED' === jobExecutionStatus) {
      fetchData()
    }

    return () => {
      isMounted = false;
    }
  }, [jobExecutionId]);

  return jobExecutionProgress;
}

export {useJobExecutionProgress};
export type {JobStatus};
