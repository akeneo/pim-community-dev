import {useMemo} from 'react';
import {useStorageState} from '@akeneo-pim-community/shared';
import {getDefaultJobExecutionFilter, JobExecutionFilter} from '../models';

const FILTER_LOCAL_STORAGE_KEY = 'process-tracker.filters';

const useStoredJobExecutionFilter = () => {
  const [jobExecutionFilter, setJobExecutionFilter] = useStorageState<JobExecutionFilter>(
    getDefaultJobExecutionFilter(),
    FILTER_LOCAL_STORAGE_KEY
  );

  const sanitizedJobExecutionFilter = useMemo(() => {
    return {
      ...getDefaultJobExecutionFilter(),
      ...jobExecutionFilter,
    };
  }, [jobExecutionFilter]);

  return [sanitizedJobExecutionFilter, setJobExecutionFilter] as const;
};

export {useStoredJobExecutionFilter};
