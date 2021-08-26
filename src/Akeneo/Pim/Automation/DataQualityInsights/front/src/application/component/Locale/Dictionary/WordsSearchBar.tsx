import React, {useRef} from 'react';
import styled from 'styled-components';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const WordsSearchBarContainer = styled(Search)`
  margin: 10px 0px 20px 0px;
`;

type WordsSearchBarProps = {
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
  resultNumber: number;
};

const WordsSearchBar = ({searchValue, onSearchChange, resultNumber}: WordsSearchBarProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  return (
    <WordsSearchBarContainer
      searchValue={searchValue}
      placeholder={translate('akeneo_data_quality_insights.dictionary.searchPlaceholder')}
      onSearchChange={onSearchChange}
      className={'filter-box'}
      inputRef={inputRef}
    >
      <Search.ResultCount>
        {translate('pim_common.result_count', {itemsCount: resultNumber}, resultNumber)}
      </Search.ResultCount>
    </WordsSearchBarContainer>
  );
};

export {WordsSearchBar};
