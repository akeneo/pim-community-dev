import {useEffect, useState} from 'react';
import {useDebounce} from '@akeneo-pim-community/shared';

const useSearchableCollection = <Entity>(
  currentProjectCode: string,
  isDropdownOpen: boolean,
  elementsPerPage: number,
  fetchResultsCallback: (
    currentProjectCode: string,
    searchTerm: string,
    elementsPerPage: number,
    searchPage: number
  ) => Promise<Entity[]>
) => {
  const [collection, setCollection] = useState<Entity[]>([]);
  const [lastLoadedPage, setLastLoadedPage] = useState<number>(0);
  const [isFetching, setIsFetching] = useState<boolean>(false);
  const [previousSearch, setPreviousSearch] = useState<string>('');
  const [searchPage, setSearchPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState('');
  const debouncedSearchTerm = useDebounce(searchTerm, 250);
  const debouncedSearchPage = useDebounce(searchPage, 50);
  const [isSearchResults, setIsSearchResults] = useState<boolean>(false);

  const lastResultsLoaded = collection.length < lastLoadedPage * elementsPerPage;

  useEffect(() => {
    if (!debouncedSearchPage) {
      return;
    }
    if (
      !isDropdownOpen ||
      ((lastLoadedPage === debouncedSearchPage || lastResultsLoaded) && previousSearch === debouncedSearchTerm)
    ) {
      return;
    }

    (async () => {
      setIsFetching(true);

      const response = await fetchResultsCallback(
        currentProjectCode,
        debouncedSearchTerm,
        elementsPerPage,
        debouncedSearchPage
      );

      if (previousSearch !== debouncedSearchTerm) {
        setCollection(response);
        setLastLoadedPage(1);
      } else {
        setCollection([...collection, ...response]);
        setLastLoadedPage(debouncedSearchPage);
      }
      setIsFetching(false);
      setIsSearchResults(debouncedSearchTerm);
    })();
  }, [isDropdownOpen, debouncedSearchTerm, debouncedSearchPage]);

  useEffect(() => {
    setPreviousSearch(debouncedSearchTerm);
  }, [debouncedSearchTerm]);

  useEffect(() => {
    if (!isDropdownOpen) {
      setCollection([]);
      setLastLoadedPage(0);
      setPreviousSearch('');
      setSearchPage(1);
      setSearchTerm('');
    }
  }, [isDropdownOpen]);

  useEffect(() => {
    setSearchPage(1);
  }, [searchTerm]);

  return {
    collection,
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

export {useSearchableCollection};
