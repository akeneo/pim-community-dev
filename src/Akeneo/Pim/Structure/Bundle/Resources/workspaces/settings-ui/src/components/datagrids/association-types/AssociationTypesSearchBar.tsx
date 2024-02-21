import React, {useRef} from 'react';
import styled from 'styled-components';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const AssociationTypesSearchBarContainer = styled(Search)`
  margin: 10px 0 20px;
`;

type AssociationTypesSearchBarProps = {
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
  resultNumber: number;
};

const AssociationTypesSearchBar = ({searchValue, onSearchChange, resultNumber}: AssociationTypesSearchBarProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  return (
    <AssociationTypesSearchBarContainer
      searchValue={searchValue}
      placeholder={translate('pim_common.search')}
      onSearchChange={onSearchChange}
      className={'association-type-grid-search'}
      inputRef={inputRef}
    >
      <Search.ResultCount>
        {translate('pim_common.result_count', {itemsCount: resultNumber}, resultNumber)}
      </Search.ResultCount>
    </AssociationTypesSearchBarContainer>
  );
};

export {AssociationTypesSearchBar};
