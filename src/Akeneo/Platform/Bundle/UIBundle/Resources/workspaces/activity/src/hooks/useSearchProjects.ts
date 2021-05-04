import {fetchProjects, SearchProjectsParameters} from '../fetchers';
import {Project} from '../domain';
import {useSearchableCollection} from './useSearchableCollection';

const useSearchProjects = (isProjectsDropdownOpen: boolean, projectsPerPage: number, currentProjectCode: string) => {
  const searchProjects = async (_: string, searchTerm: string, projectsPerPage: number, searchPage: number) => {
    const searchParameters: SearchProjectsParameters = {
      search: searchTerm,
      options: {
        limit: projectsPerPage,
        page: searchPage,
        completeness: 1,
      },
    };
    return await fetchProjects(searchParameters);
  };

  const {
    collection,
    isFetching,
    lastResultsLoaded,
    searchPage,
    setSearchPage,
    searchTerm,
    setSearchTerm,
    debouncedSearchPage,
  } = useSearchableCollection<Project>(currentProjectCode, isProjectsDropdownOpen, projectsPerPage, searchProjects);

  return {
    projects: collection,
    isFetching,
    lastResultsLoaded,
    searchPage,
    setSearchPage,
    searchTerm,
    setSearchTerm,
    debouncedSearchPage,
  };
};

export {useSearchProjects};
