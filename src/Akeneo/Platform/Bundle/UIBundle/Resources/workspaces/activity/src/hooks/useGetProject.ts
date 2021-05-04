import {useEffect, useState} from 'react';
import {fetchNearestDueProject, fetchProject} from '../fetchers';
import {Project} from '../domain';

const useGetProject = (projectCode: string) => {
  const [project, setProject] = useState<Project | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    (async () => {
      setIsLoading(true);
      if (!projectCode) {
        const response = await fetchNearestDueProject();
        if (response.length > 0) {
          setProject(response[0]);
        }
      } else {
        const response = await fetchProject(projectCode);
        setProject(response);
      }
      setIsLoading(false);
    })();
  }, [projectCode]);

  return {
    project,
    isLoading,
  };
};

export {useGetProject};
