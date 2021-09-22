import {useTranslate} from '@akeneo-pim-community/shared';
import {CellMatcher} from './index';

const useSearch: CellMatcher = () => {
  const translate = useTranslate();

  return (cell, searchText) => {
    const isSearching = searchText !== '';
    if (!isSearching || typeof cell === 'undefined') {
      return false;
    }

    return translate(cell ? 'pim_common.yes' : 'pim_common.no')
      .toLowerCase()
      .includes(searchText.toLowerCase());
  };
};

export default useSearch;
