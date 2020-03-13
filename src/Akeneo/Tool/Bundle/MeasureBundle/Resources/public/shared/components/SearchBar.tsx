import React, {useContext, ChangeEvent} from 'react';
import styled from 'styled-components';
import {SearchIcon} from 'akeneomeasure/shared/icons/SearchIcon';
import {ResultCount} from 'akeneomeasure/shared/components/ResultCount';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {useFocus} from 'akeneomeasure/shared/hooks/use-focus';

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
  flex: 1;
  align-items: center;
`;

const SearchInput = styled.input`
  border: none;
  flex: 1;
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

const SearchBar = ({className, count, searchValue, onSearchChange}: SearchBarProps) => {
  const __ = useContext(TranslateContext);
  const [searchFocusRef] = useFocus();

  return (
    <Container className={className}>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          ref={searchFocusRef}
          placeholder={__('measurements.search.placeholder')}
          value={searchValue}
          onChange={(event: ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      <ResultCount count={count} />
    </Container>
  );
};

export {SearchBar};
