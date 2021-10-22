import React, {useRef} from 'react';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type MeasurementFamilySearchBarProps = {
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
  resultNumber: number;
};

const MeasurementFamilySearchBar = ({searchValue, onSearchChange, resultNumber}: MeasurementFamilySearchBarProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  return (
    <Search
      sticky={0}
      placeholder={translate('measurements.search.placeholder')}
      searchValue={searchValue}
      onSearchChange={onSearchChange}
      inputRef={inputRef}
    >
      <Search.ResultCount>
        {translate('pim_common.result_count', {itemsCount: resultNumber}, resultNumber)}
      </Search.ResultCount>
    </Search>
  );
};

export {MeasurementFamilySearchBar};
