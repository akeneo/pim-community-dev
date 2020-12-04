import {useCallback, useEffect, useState} from 'react';

import {AttributeOption} from '../model';

type UseSortedAttributeOptionsState = {
  sortedAttributeOptions: AttributeOption[] | null;
  moveAttributeOption: (sourceOptionCode: string, targetOptionCode: string) => void;
  validateMoveAttributeOption: () => void;
};

export const useSortedAttributeOptions = (
  attributeOptions: AttributeOption[] | null,
  autoSortOptions: boolean,
  manuallySortAttributeOptions: (attributeOptions: AttributeOption[]) => void
): UseSortedAttributeOptionsState => {
  const [sortedAttributeOptions, setSortedAttributeOptions] = useState<AttributeOption[] | null>(attributeOptions);
  const moveAttributeOption = useCallback(
    (sourceOptionCode: string, targetOptionCode: string) => {
      if (sortedAttributeOptions !== null && sourceOptionCode !== targetOptionCode) {
        const sourceIndex = sortedAttributeOptions.findIndex(
          (attributeOption: AttributeOption) => attributeOption.code === sourceOptionCode
        );
        const targetIndex = sortedAttributeOptions.findIndex(
          (attributeOption: AttributeOption) => attributeOption.code === targetOptionCode
        );
        const sourceOption = sortedAttributeOptions[sourceIndex];

        let newSortedAttributeOptions = [...sortedAttributeOptions];
        newSortedAttributeOptions.splice(sourceIndex, 1);
        newSortedAttributeOptions.splice(targetIndex, 0, sourceOption);

        setSortedAttributeOptions(newSortedAttributeOptions);
      }
    },
    [sortedAttributeOptions]
  );

  const validateMoveAttributeOption = useCallback(() => {
    if (
      sortedAttributeOptions !== null &&
      JSON.stringify(sortedAttributeOptions) !== JSON.stringify(attributeOptions)
    ) {
      manuallySortAttributeOptions(sortedAttributeOptions);
    }
  }, [sortedAttributeOptions]);

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
    moveAttributeOption,
    validateMoveAttributeOption,
  };
};
