import {useEffect, useState} from 'react';

import {AttributeOption} from '../model';

type UseSortedAttributeOptionsState = {
  sortedAttributeOptions: AttributeOption[] | null;
  setSortedAttributeOptions: (attributeOptions: (rows: AttributeOption[]) => AttributeOption[]) => void;
};

export const useSortedAttributeOptions = (
  attributeOptions: AttributeOption[] | null,
  autoSortOptions: boolean
): UseSortedAttributeOptionsState => {
  const [sortedAttributeOptions, setSortedAttributeOptions] = useState<AttributeOption[] | null>(attributeOptions);

  useEffect(() => {
    if (attributeOptions !== null) {
      let sortedOptions = [...attributeOptions];
      if (autoSortOptions) {
        // /!\ sort() does not return another reference, it sorts directly on the original variable.
        sortedOptions.sort((option1: AttributeOption, option2: AttributeOption) => {
          return option1.code.localeCompare(option2.code, undefined, {sensitivity: 'base'});
        });
      }
      setSortedAttributeOptions(sortedOptions);
    }
  }, [attributeOptions, autoSortOptions]);

  return {
    sortedAttributeOptions,
    setSortedAttributeOptions,
  };
};
