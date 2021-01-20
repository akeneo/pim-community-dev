import React, {ChangeEvent, useRef} from 'react';
import styled from 'styled-components';
import {useAutoFocus} from '../hooks';
import {AkeneoThemedProps, SearchIcon} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy';

const Container = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey100};
  background: ${({theme}: AkeneoThemedProps) => theme.color.white};
  position: sticky;
  top: 0;
  height: 44px;
  flex: 1;
  z-index: 1;
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
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey120};
  outline: none;

  ::placeholder {
    color: ${({theme}: AkeneoThemedProps) => theme.color.grey120};
  }
`;

const ResultCount = styled.div`
  white-space: nowrap;
  color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

type SearchBarProps = {
  className?: string;
  placeholder?: string;
  count: number;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
};

const SearchBar = ({className, placeholder, count, searchValue, onSearchChange}: SearchBarProps) => {
  const translate = useTranslate();
  const searchFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(searchFieldRef);

  return (
    <Container className={className}>
      <SearchContainer>
        <SearchIcon />
        <SearchInput
          title={translate('pim_common.search')}
          ref={searchFieldRef}
          placeholder={placeholder || translate('pim_common.search')}
          value={searchValue}
          onChange={(event: ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      <ResultCount>{translate('pim_common.result_count', {itemsCount: count.toString()}, count)}</ResultCount>
    </Container>
  );
};

export {SearchBar};
