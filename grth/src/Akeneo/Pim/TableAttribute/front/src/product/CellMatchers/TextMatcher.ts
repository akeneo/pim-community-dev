import {CellMatcher} from './index';

const useSearch: CellMatcher = () => {
  return (cell, searchText) => {
    const isSearching = searchText !== '';
    if (!isSearching || typeof cell === 'undefined') {
      return false;
    }

    return `${cell}`.toLowerCase().includes(searchText.toLowerCase());
  };
};

export default useSearch;
