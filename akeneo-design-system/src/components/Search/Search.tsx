import React, {ChangeEvent, ReactNode, useRef} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';
import {SearchIcon} from '../../icons';
import {useAutoFocus} from '../../hooks';
import {ResultCount} from './ResultCount';

type SearchProps = {
  children?: ReactNode;
  placeholder?: string;
  title?: string;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
};

const Search = ({children, placeholder, title, searchValue, onSearchChange}: SearchProps) => {
  const searchFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(searchFieldRef);

  const decoratedChildren = React.Children.map(children, (child, index) => {
    return index === 0 ? child : <FilterContainer>{child}</FilterContainer>;
  });

  return (
    <Container>
      <SearchContainer>
        <SearchIcon width={20} height={20} />
        <SearchInput
          title={title}
          ref={searchFieldRef}
          placeholder={placeholder}
          value={searchValue}
          onChange={(event: ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      {decoratedChildren}
    </Container>
  );
};

const Container = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${getColor('grey', 100)};
  background: ${getColor('white')};
  position: sticky;
  top: 0;
  height: 44px;
  flex: 1;
  z-index: 1;
  box-sizing: border-box;

  :focus-within {
    border-bottom: 1px solid ${getColor('purple', 100)};
  }
`;

const SearchContainer = styled.div`
  display: flex;
  flex: 1;
  align-items: center;
`;

const SearchInput = styled.input`
  border: none;
  flex: 1;
  margin-left: 6px;
  color: ${getColor('grey', 140)};
  outline: none;

  ::placeholder {
    color: ${getColor('grey', 120)};
  }
`;

const FilterContainer = styled.div`
  margin-left: 20px;
  border-left: 1px ${getColor('grey', 100)} solid;
  padding-left: 20px;
  height: 24px;
  display: flex;
`;

Search.ResultCount = ResultCount;

export {Search};
