import React, {FC, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {getBodyStyle} from '../../typography';
import {AkeneoThemedProps, getColor} from '../../theme';

const currentPaginationItemMixin = css`
  border: 1px ${getColor('brand', 100)} solid;
  ${getBodyStyle({
    size: 'regular',
    color: 'brand',
    gradient: 100,
    weight: 'regular',
  })}
`;

const otherPaginationItemMixin = css`
  border: 1px ${getColor('grey', 80)} solid;
  ${getBodyStyle({
    size: 'regular',
    color: 'grey',
    gradient: 80,
    weight: 'regular',
  })}
`;

const disabledMixin = css`
  cursor: default;
  :hover {
    background-color: ${getColor('white')};
  }
`;

const PaginationItemContainer = styled.button<AkeneoThemedProps & {disabled: boolean; currentPage: boolean}>`
  ${({currentPage}) => (currentPage ? currentPaginationItemMixin : otherPaginationItemMixin)}
  display: inline-block;
  border-width: 1px;
  font-size: 13px;
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
  background-color: ${getColor('white')};

  :hover {
    background-color: ${getColor('grey', 20)};
  }

  :focus {
    outline: 0;
  }

  ${({disabled}) => (disabled ? disabledMixin : null)}
`;

const PAGINATION_SEPARATOR = '…';

type PaginationItemProps = {
  currentPage: boolean;
  page: string;
  followPage: (page: number) => void;
};

const PaginationItem: FC<PaginationItemProps> = ({currentPage, page, followPage}) => {
  const handleClick = useCallback(() => {
    if (page !== PAGINATION_SEPARATOR) {
      followPage(parseInt(page));
    }
  }, [page, followPage]);

  return (
    <PaginationItemContainer
      onClick={handleClick}
      data-testid="paginationItem"
      title={page !== PAGINATION_SEPARATOR ? `No. ${page}` : ''}
      disabled={page === PAGINATION_SEPARATOR}
      currentPage={currentPage}
      page={page}
      type="button"
    >
      {page}
    </PaginationItemContainer>
  );
};

export {PaginationItem, PAGINATION_SEPARATOR};
