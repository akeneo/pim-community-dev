import React, {ChangeEvent, HTMLAttributes, ReactNode, RefObject} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {SearchIcon} from '../../icons';
import {Override} from '../../shared';

const Container = styled.div<{sticky?: number} & AkeneoThemedProps>`
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid ${getColor('grey', 100)};
  background: ${getColor('white')};
  height: 44px;
  box-sizing: border-box;
  gap: 10px;

  &:focus-within {
    border-bottom: 1px solid ${getColor('brand', 100)};
  }

  ${({sticky}) =>
    undefined !== sticky &&
    css`
      position: sticky;
      top: ${sticky}px;
      z-index: 9;
    `}
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
  margin-left: 10px;
  border-left: 1px ${getColor('grey', 100)} solid;
  padding-left: 10px;
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

type SearchProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Content of the Search component.
     */
    children?: ReactNode;

    /**
     * Placeholder displayed when the search input is empty.
     */
    placeholder?: string;

    /**
     * Text displayed on the rollover of the Search component.
     */
    title?: string;

    /**
     * The search string.
     */
    searchValue: string;

    /**
     * Ref to forward to the input field.
     */
    inputRef?: RefObject<HTMLInputElement>;

    /**
     * When set, defines the sticky top position of the Search component.
     */
    sticky?: number;

    /**
     * Handler called when the search input is updated.
     */
    onSearchChange: (searchValue: string) => void;
  }
>;

const Search = ({children, placeholder, title, searchValue, inputRef, onSearchChange, ...rest}: SearchProps) => {
  return (
    <Container {...rest}>
      <SearchContainer>
        <SearchIcon size={20} />
        <SearchInput
          ref={inputRef}
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
