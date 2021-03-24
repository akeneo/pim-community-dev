import React, {ChangeEvent, ReactNode} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';
import {SearchIcon} from '../../icons';

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
    border-bottom: 1px solid ${getColor('brand', 100)};
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

const Separator = styled.div`
  margin-left: 20px;
  border-left: 1px ${getColor('grey', 100)} solid;
  padding-left: 20px;
  height: 24px;
  display: flex;
`;

const ResultCount = styled.span`
  white-space: nowrap;
  color: ${getColor('brand', 100)};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

type SearchProps = {
  /**
   * Content of the Search component
   */
  children?: ReactNode;

  /**
   * Placeholder displayed when the search input is empty.
   */
  placeholder?: string;

  /**
   * Text displayed on the rollover of the Search component
   */
  title?: string;

  /**
   * The search string
   */
  searchValue: string;

  /**
   * Handle called when the search input is updated
   */
  onSearchChange: (searchValue: string) => void;
};

const Search = ({children, placeholder, title, searchValue, onSearchChange}: SearchProps) => {
  return (
    <Container>
      <SearchContainer>
        <SearchIcon size={20} />
        <SearchInput
          title={title}
          placeholder={placeholder}
          value={searchValue}
          onChange={(event: ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value)}
        />
      </SearchContainer>
      {children}
    </Container>
  );
};

Search.ResultCount = ResultCount;
Search.Separator = Separator;

export {Search};
