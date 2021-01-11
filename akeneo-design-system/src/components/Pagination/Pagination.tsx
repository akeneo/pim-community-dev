import React, {FC} from 'react';
import styled from 'styled-components';
import {PAGINATION_SEPARATOR, PaginationItem} from './PaginationItem';

type PaginationProps = {
  /**
   * The current page number (starting at 1).
   */
  currentPage: number;

  /**
   * The total number of results.
   */
  totalItems: number;

  /**
   * The number of items per page. Default to 25.
   */
  itemsPerPage?: number;

  /**
   * Handler called when a pagination item is clicked.
   */
  onClick: (page: number) => void;
};

const MAX_PAGINATION_ITEMS_WITHOUT_SEPARATOR = 4;

const Pagination: FC<PaginationProps> = ({currentPage, totalItems, itemsPerPage = 25, onClick}) => {
  if (itemsPerPage <= 0) {
    return <></>;
  }

  const numberOfPages = Math.ceil(totalItems / itemsPerPage);

  if (currentPage > numberOfPages) {
    throw new Error('');
  }

  if (numberOfPages <= 1) {
    return <></>;
  }

  const pages = computePages(currentPage, numberOfPages);

  return (
    <PaginationContainer>
      {pages.map((page: number | string, index: number) => {
        return (
          <PaginationItem currentPage={page === currentPage} key={index} onClick={onClick} page={page as string} />
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
  gap: 10px;
`;

function computePages(currentPage: number, numberOfPages: number) {
  if (numberOfPages <= MAX_PAGINATION_ITEMS_WITHOUT_SEPARATOR) {
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
