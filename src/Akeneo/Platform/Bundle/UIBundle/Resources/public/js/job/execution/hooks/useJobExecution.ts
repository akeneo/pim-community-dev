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
  const isDocumentVisible = useDocumentVisibility();
  const willRefresh = isDocumentVisible && !isJobFinished(jobExecution);
  const [isFetching, setIsFetching] = useState<boolean>(false);

  const fetchJobExecution = useCallback(async () => {
    if (isFetching) {
      return;
    }
    if (isMounted()) {
      setIsFetching(true);
    }
    const response = await fetch(route);
    if (isMounted()) {
      setIsFetching(false);
    }
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
  }, [route, isFetching]);

  useEffect(() => {
    if (!willRefresh) return;

    const interval = setInterval(fetchJobExecution, 1000);

    return () => {
      clearInterval(interval);
    };
  }, [willRefresh, isFetching]);

  return [jobExecution, error, fetchJobExecution, willRefresh] as const;
};

export {useJobExecution};
