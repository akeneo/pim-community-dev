import React, {ChangeEvent, useRef} from 'react';
import styled from 'styled-components';
import {ResultCount} from './ResultCount';
import {SearchIcon} from '../icons';
import {useAutoFocus} from '../hooks';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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
  placeholder?: string;
  count: number;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
};

const SearchBar = ({className, placeholder, count, searchValue, onSearchChange}: SearchBarProps) => {
  const __ = useTranslate();
  const searchFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(searchFieldRef);

  return (
    <Container className={className}>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          ref={searchFieldRef}
          placeholder={placeholder || __('pim_common.search')}
          value={searchValue}
          onChange={(event: ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      <ResultCount count={count} />
    </Container>
  );
};

export {SearchBar};
