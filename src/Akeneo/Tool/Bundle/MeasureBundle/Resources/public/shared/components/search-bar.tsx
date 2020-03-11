import React, {useContext} from 'react';
import styled from 'styled-components';
import {Search as SearchIcon} from 'akeneomeasure/shared/icons/search';
import {ResultCount} from 'akeneomeasure/shared/components/result-count';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {TranslateContext} from 'akeneomeasure/context/translate-context';

const Container = styled.div`
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid ${akeneoTheme.color.grey100};
  padding: 13px 0;
  margin: 20px 0;
`;

const SearchContainer = styled.div`
  display: flex;
  align-items: center;
`;

const SearchInput = styled.input`
  border: none;
  width: 180px;
  margin-left: 5px;
  color: ${akeneoTheme.color.grey120};
  outline: none;

  ::placeholder {
    color: ${akeneoTheme.color.grey120};
  }
`;

type SearchBarProps = {
  count: number;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
};

export const SearchBar = ({count, searchValue, onSearchChange}: SearchBarProps) => {
  const __ = useContext(TranslateContext);

  return (
    <Container>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          placeholder={__('measurements.search.placeholder')}
          value={searchValue}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onSearchChange(event.target.value);
          }}
        />
      </SearchContainer>
      <ResultCount count={count} />
    </Container>
  );
};
