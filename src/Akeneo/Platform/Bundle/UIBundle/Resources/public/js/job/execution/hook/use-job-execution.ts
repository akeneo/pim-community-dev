import {useIsMounted} from '@akeneo-pim-community/shared/src';
import {useCallback, useEffect, useState} from 'react';
import {JobExecution} from '../model/job-execution';
import {useRouter} from '@akeneo-pim-community/legacy-bridge/src';

type Error = {
  statusMessage: any;
  statusCode: number;
};

const useJobExecution = (jobExecutionId: string) => {
  const router = useRouter();
  const isMounted = useIsMounted();
  const [jobExecution, setJobExecution] = useState<JobExecution | null>(null);
  const [error, setError] = useState<Error | null>(null);
  const route = router.generate('pim_enrich_job_execution_rest_get', {identifier: jobExecutionId});

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
    (async () => {
      await fetchJobExecution();
    })();
  }, []);

  return {jobExecution, error, reloadJobExecution: fetchJobExecution};
};

export {useJobExecution};
