import {fetchContributors, SearchContributorsParameters} from '../fetchers';
import {Contributor} from '../domain';
import {useSearchableCollection} from './useSearchableCollection';

const useSearchContributors = (
  isContributorsDropdownOpen: boolean,
  contributorsPerPage: number,
  currentProjectCode: string
) => {
  const searchContributors = async (
    currentProjectCode: string,
    searchTerm: string,
    contributorsPerPage: number,
    searchPage: number
  ) => {
    const searchParameters: SearchContributorsParameters = {
      identifier: currentProjectCode,
      search: searchTerm,
      options: {
        limit: contributorsPerPage,
        page: searchPage,
      },
    };
    return await fetchContributors(searchParameters);
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
    isSearchResults,
  } = useSearchableCollection<Contributor>(
    currentProjectCode,
    isContributorsDropdownOpen,
    contributorsPerPage,
    searchContributors
  );

  return {
    contributors: collection,
    isFetching,
    lastResultsLoaded,
    searchPage,
    setSearchPage,
    searchTerm,
    setSearchTerm,
    debouncedSearchPage,
    isSearchResults,
  };
};

export {useSearchContributors};
