import React, {ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {ArrowDownIcon, ArrowUpIcon} from '../../../icons';

type TableSortDirection = 'descending' | 'ascending' | 'none';

type TableHeaderCellProps = {
  /**
   * Define if the header can be sorted
   */
  isSortable?: boolean;

  /**
   * Function called when the user click on sort icon
   */
  onDirectionChange?: (direction: TableSortDirection) => void;

  /**
   * Define the sort direction
   */
  sortDirection?: TableSortDirection;

  /**
   * Content of the header cell
   */
  children?: ReactNode;
};

const HeaderCellContainer = styled.th<{isSortable: boolean; isSorted: boolean} & AkeneoThemedProps>`
  background: linear-gradient(to top, #67768a 1px, white 0px);
  height: 44px;
  text-align: left;
  color: ${props => (props.isSorted ? getColor('purple', 100) : getColor('grey', 100))};
  font-weight: normal;

  ${({isSortable}) =>
    isSortable &&
    css`
      cursor: pointer;
    `};
`;

const HeaderCellContentContainer = styled.span`
  color: ${getColor('grey', 140)};
  padding: 0 10px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  + svg {
    vertical-align: middle;
  }
`;

const TableHeaderCell = ({
  isSortable = false,
  onDirectionChange,
  sortDirection,
  children,
  ...rest
}: TableHeaderCellProps) => {
  if (isSortable && (onDirectionChange === undefined || sortDirection === undefined)) {
    throw Error('Sortable header should provide onDirectionChange and direction props');
  }

  const handleClick = () => {
    if (!isSortable || onDirectionChange === undefined) return;

    switch (sortDirection) {
      case 'ascending':
        onDirectionChange('descending');
        break;
      case 'descending':
      case 'none':
        onDirectionChange('ascending');
        break;
    }
  };

  return (
    <HeaderCellContainer
      isSorted={sortDirection !== 'none'}
      isSortable={isSortable}
      aria-sort={sortDirection}
      onClick={handleClick}
      {...rest}
    >
      <HeaderCellContentContainer>{children}</HeaderCellContentContainer>
      {isSortable &&
        (sortDirection == 'descending' || sortDirection == 'none' ? (
          <ArrowDownIcon size={14} />
        ) : (
          <ArrowUpIcon size={14} />
        ))}
    </HeaderCellContainer>
  );
};

export {TableHeaderCell};
