import React, {useContext, ChangeEvent, useRef} from 'react';
import styled from 'styled-components';
import {SearchIcon} from 'akeneomeasure/shared/icons/SearchIcon';
import {ResultCount} from 'akeneomeasure/shared/components/ResultCount';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {useAutoFocus} from 'akeneomeasure/shared/hooks/use-auto-focus';

const Container = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${props => props.theme.color.grey100};
  background: ${props => props.theme.color.white};
  position: sticky;
  top: 0;
  height: 44px;
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
  const searchFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(searchFieldRef);

  return (
    <Container className={className}>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          ref={searchFieldRef}
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
