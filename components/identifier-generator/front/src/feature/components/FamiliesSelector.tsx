import React, {FC} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {usePaginatedFamilies} from '../hooks/useFamilies';

type FamiliesSelectorProps = {};

const FamiliesSelector: FC<FamiliesSelectorProps> = () => {
  const {families, handleNextPage, handleSearchChange} = usePaginatedFamilies();

  const handleChange = (value: string[]) => {
    value = ['foo'];
  };

  return <MultiSelectInput
    emptyResultLabel="No result found"
    onChange={handleChange}
    placeholder="Please enter a value in the Multi select input"
    removeLabel="Remove"
    value={[]}
    openLabel='open'
    onNextPage={handleNextPage}
    onSearchChange={handleSearchChange}
  >
    {(families || []).map(family => <MultiSelectInput.Option value={family.code} key={family.code}>
      {family.code}
    </MultiSelectInput.Option>)}
  </MultiSelectInput>;
};

export {FamiliesSelector};
