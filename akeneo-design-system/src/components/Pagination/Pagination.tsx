import React, {FC} from 'react';
import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps} from '../../theme';

type PaginationProps = {
  currentPage: number;
  itemsTotal: number;
  itemsPerPage?: number;
  onClick: (page: number) => void;
};

const MAX_PAGINATION_ITEMS = 7;
const PAGINATION_SEPARATOR = '...';

const Pagination: FC<PaginationProps> = ({currentPage, itemsTotal, itemsPerPage = 25, onClick}) => {
  if (itemsPerPage <= 0) {
    return <></>;
  }

  const numberOfPages = Math.ceil(itemsTotal / itemsPerPage);

  if (numberOfPages <= 1 || currentPage > numberOfPages) {
    return <></>;
  }

  const pages = computePages(currentPage, numberOfPages);

  return (
    <PaginationContainer>
      {pages.map((page: number | string, index: number) => {
        return (
          <PaginationItem currentPage={page === currentPage} key={index} onClick={onClick}>
            {page}
          </PaginationItem>
        );
      })}
    </PaginationContainer>
  );
};

const PaginationContainer = styled.div`
  height: 44px;
  margin: 10px 0 10px 0;
  align-items: center;
  display: flex;
  justify-content: center;
`;

type PaginationItemProps = {
  currentPage: boolean;
  onClick: (page: number) => void;
};

const PaginationItem: FC<PaginationItemProps> = ({currentPage, children, onClick}) => {
  const title = `No. ${children as string}`;

  return (
    <PaginationItemContainer
      onClick={() => (children == PAGINATION_SEPARATOR ? null : onClick(children as number))}
      data-testid="paginationItem"
      title={children != PAGINATION_SEPARATOR ? title : ''}
      disabled={children == PAGINATION_SEPARATOR}
      currentPage={currentPage}
    >
      {children}
    </PaginationItemContainer>
  );
};

const currentPaginationItemMixin = css`
  border: 1px ${getColor('brand', 100)} solid;
  color: ${getColor('brand', 100)};
`;

const otherPaginationItemMixin = css`
  border: 1px ${getColor('grey', 80)} solid;
  color: ${getColor('grey', 100)};
`;

const disabledMixin = css`
  cursor: default;
  :hover {
    background-color: ${getColor('white')};
  }
`;

const PaginationItemContainer = styled.span<AkeneoThemedProps & PaginationItemProps & {disabled: boolean}>`
  ${props => (props.currentPage ? currentPaginationItemMixin : otherPaginationItemMixin)}
  display: inline-block;
  border-width: 1px;
  font-size: 13px;
  font-weight: 400;
  text-transform: uppercase;
  border-radius: 16px;
  padding: 0 10px;
  height: 22px;
  line-height: 21px;
  cursor: pointer;
  font-family: inherit;
  transition: background-color 0.1s ease 0s;
  min-width: 40px;
  text-align: center;
  box-sizing: border-box;

  :not(:last-child) {
    margin-right: 10px;
  }

  :hover {
    background-color: ${getColor('grey', 20)};
  }

  ${props => (props.disabled ? disabledMixin : null)}
`;

function computePages(currentPage: number, numberOfPages: number) {
  if (numberOfPages <= MAX_PAGINATION_ITEMS) {
    return Array.from(Array(numberOfPages).keys()).map((page: number) => page + 1);
  }

  const FIRST_PAGE = 1;
  const SECOND_PAGE = 2;
  const THIRD_PAGE = 3;
  const FOURTH_PAGE = 4;
  const LAST_PAGE = numberOfPages;
  const SECOND_LAST = LAST_PAGE - 1;
  const THIRD_LAST = LAST_PAGE - 2;
  const FOURTH_LAST = LAST_PAGE - 3;
  const PREVIOUS_PAGE = currentPage - 1;
  const NEXT_PAGE = currentPage + 1;

  const pages: Array<number | string> = [FIRST_PAGE];

  if (currentPage >= FOURTH_PAGE) {
    pages.push(PAGINATION_SEPARATOR);
  }

  if (currentPage > SECOND_PAGE) {
    if (currentPage === LAST_PAGE) {
      pages.push(THIRD_LAST);
    }
    pages.push(PREVIOUS_PAGE);
  }

  if (currentPage !== FIRST_PAGE && currentPage !== LAST_PAGE) {
    pages.push(currentPage);
  }

  if (currentPage < SECOND_LAST) {
    pages.push(NEXT_PAGE);
  }

  if (currentPage === FIRST_PAGE) {
    pages.push(THIRD_PAGE);
  }

  if (currentPage <= FOURTH_LAST) {
    pages.push(PAGINATION_SEPARATOR);
  }

  pages.push(LAST_PAGE);

  return pages;
}

export {Pagination};
