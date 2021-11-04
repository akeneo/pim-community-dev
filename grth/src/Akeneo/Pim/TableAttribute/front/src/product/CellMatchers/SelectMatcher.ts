import React from 'react';
import {useFetchOptions} from '../useFetchOptions';
import {CellMatcher} from './index';
import {useAttributeContext} from '../../contexts/AttributeContext';

const useSearch: CellMatcher = () => {
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionLabel} = useFetchOptions(attribute, setAttribute);

  return (cell, searchText, columnCode) => {
    const isSearching = searchText !== '';
    if (!isSearching || typeof cell === 'undefined') {
      return false;
    }

    const label = getOptionLabel(columnCode, cell as string);
    return !!label && label.toLowerCase().includes(searchText.toLowerCase());
  };
};

export default useSearch;
