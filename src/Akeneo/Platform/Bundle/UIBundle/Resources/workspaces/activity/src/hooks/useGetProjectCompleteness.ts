import {useCallback, useEffect, useState} from 'react';
import {ProjectCompletenessType} from '../domain';
import {fetchProjectCompleteness} from '../fetchers';

const POLLING_RATE = 5000;

const useGetProjectCompleteness = (projectCode: string, contributorUsername: string | null) => {
  const [projectCompleteness, setProjectCompleteness] = useState<ProjectCompletenessType | null>(null);

  const pollProjectCompleteness = useCallback(async () => {
    const response = await fetchProjectCompleteness(projectCode, contributorUsername);
    if (response) {
      setProjectCompleteness(response);
    }
  }, [projectCode, contributorUsername, fetchProjectCompleteness, setProjectCompleteness]);

  useEffect(() => {
    if (!projectCode || (projectCompleteness && projectCompleteness.is_completeness_computed)) {
      return;
    }

    const interval = setInterval(pollProjectCompleteness, POLLING_RATE);

    return () => {
      clearInterval(interval);
    };
  }, [projectCode, projectCompleteness]);

  useEffect(() => {
    if (projectCode) {
      pollProjectCompleteness();
    }
  }, [projectCode, contributorUsername]);

  return projectCompleteness;
};

export {useGetProjectCompleteness};
