import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from "react-query";
import {isJobFinished, JobExecution} from "../models";
import {useState} from "react";

class QueryError extends Error {
  public statusCode: number;

  constructor(statusMessage: string, statusCode: number) {
    super(statusMessage);
    this.statusCode = statusCode;
  }
}

const useJobExecution = (jobExecutionId: string) => {
  const [willRefresh, setWillRefresh] = useState(true);
  const route = useRoute('pim_enrich_job_execution_rest_get', {identifier: jobExecutionId});
  const fetchJobExecution = async () => {
    const response = await fetch(route);
    if (!response.ok) {
      throw new QueryError(response.statusText, response.status);
    }

    const jobExecution = await response.json();
    if (isJobFinished(jobExecution)) {
      setWillRefresh(false);
    }

    return jobExecution;
  };

  const {data, error, refetch} = useQuery<JobExecution, QueryError>(
    'process_tracker_job_execution',
    fetchJobExecution,
    {
      /** TODO fix it */
      enabled: willRefresh,
      refetchInterval: 1000,
      refetchOnWindowFocus: true
    }
  );

  return [data ?? null, error, refetch, willRefresh] as const;
};

export {useJobExecution};
