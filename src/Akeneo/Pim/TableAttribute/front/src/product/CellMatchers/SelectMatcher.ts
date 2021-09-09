import {useFetchOptions} from '../useFetchOptions';
import {CellMatcher} from './index';

const useSearch: CellMatcher = (attribute, valueData) => {
  const {getOptionLabel} = useFetchOptions(attribute.table_configuration, attribute.code, valueData);

  return (cell, searchText, columnCode) => {
    const isSearching = searchText !== '';
    if (!isSearching || typeof cell === 'undefined') {
      return false;
    }

    const label = getOptionLabel(columnCode, cell);
    return !!label && label.toLowerCase().includes(searchText.toLowerCase());
  };
};

export default useSearch;
