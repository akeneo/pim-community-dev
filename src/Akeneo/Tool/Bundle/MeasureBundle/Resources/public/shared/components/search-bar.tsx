import React, {useContext} from 'react';
import styled from 'styled-components';
import {Search as SearchIcon} from 'akeneomeasure/shared/icons/search';
import {ResultCount} from 'akeneomeasure/shared/components/result-count';
import {TranslateContext} from 'akeneomeasure/context/translate-context';

const Container = styled.div`
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid ${props => props.theme.color.grey100};
  background: ${props => props.theme.color.white};
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
  color: ${props => props.theme.color.grey120};
  outline: none;

  ::placeholder {
    color: ${props => props.theme.color.grey120};
  }
`;

type SearchBarProps = {
  className?: string;
  count: number;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
};

export const SearchBar = ({className, count, searchValue, onSearchChange}: SearchBarProps) => {
  const __ = useContext(TranslateContext);

  return (
    <Container className={className}>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          placeholder={__('measurements.search.placeholder')}
          value={searchValue}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      <ResultCount count={count} />
    </Container>
  );
};
