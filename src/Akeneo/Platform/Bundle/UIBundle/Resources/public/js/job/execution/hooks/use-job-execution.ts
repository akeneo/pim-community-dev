import {useCallback, useEffect, useState} from 'react';
import {useIsMounted} from '@akeneo-pim-community/shared';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {JobExecution} from '../models';
import {useDocumentVisibility} from '@akeneo-pim-community/shared/src/hooks/useDocumentVisibility';

type Error = {
  statusMessage: string;
  statusCode: number;
};

const useJobExecution = (jobExecutionId: string) => {
  const router = useRouter();
  const isMounted = useIsMounted();
  const [jobExecution, setJobExecution] = useState<JobExecution | null>(null);
  const [error, setError] = useState<Error | null>(null);
  const route = router.generate('pim_enrich_job_execution_rest_get', {identifier: jobExecutionId});
  const documentIsVisible = useDocumentVisibility();
  const isFinished = jobExecution !== undefined && ['COMPLETED', 'STOPPED', 'FAILED'].includes(jobExecution?.tracking.status ?? '');
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

    const interval = setInterval(() => {
      fetchJobExecution();
    }, 1000);

    return () => {
      clearInterval(interval);
    };
  }, [documentIsVisible, isFinished]);


  return {jobExecution, error, isFinished, reloadJobExecution: fetchJobExecution};
};

export {useJobExecution};
