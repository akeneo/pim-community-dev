import {useCallback, useEffect, useState} from 'react';
import {useIsMounted, useDocumentVisibility} from '@akeneo-pim-community/shared';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {isJobFinished, JobExecution} from '../models';

type Error = {
  statusMessage: string;
  statusCode: number;
};

const useJobExecution = (jobExecutionId: string) => {
  const isMounted = useIsMounted();
  const [jobExecution, setJobExecution] = useState<JobExecution | null>(null);
  const [error, setError] = useState<Error | null>(null);
  const route = useRoute('pim_enrich_job_execution_rest_get', {identifier: jobExecutionId});
  const documentIsVisible = useDocumentVisibility();
  const isFinished = isJobFinished(jobExecution);

  const fetchJobExecution = useCallback(async () => {
    const response = await fetch(route);

    if (!response.ok) {
      setError({
        statusMessage: response.statusText,
        statusCode: response.status,
      });

      return;
    }

    const jobExecution = await response.json();

    if (isMounted()) {
      setJobExecution(jobExecution);
    }
  }, [route]);

  useEffect(() => {
    if (!documentIsVisible || isFinished) return;

    const interval = setInterval(fetchJobExecution, 1000);

    return () => {
      clearInterval(interval);
    };
  }, [documentIsVisible, isFinished]);

  return [jobExecution, error, fetchJobExecution] as const;
};

export {useJobExecution};
