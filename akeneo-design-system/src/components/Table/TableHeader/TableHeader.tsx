import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {SelectableContext} from '../SelectableContext';

const TableHead = styled.thead<{sticky?: number} & AkeneoThemedProps>`
  ${({sticky}) =>
    undefined !== sticky &&
    css`
      th {
        position: sticky;
        top: ${sticky}px;
        background-color: ${getColor('white')};
      }
    `}
`;

type TableHeaderProps = {
  /**
   * When set, defines the top position of the Header cells.
   */
  sticky?: number;

  /**
   * Header cells.
   */
  children?: ReactNode;
};

const TableHeader = React.forwardRef<HTMLTableSectionElement, TableHeaderProps>(
  ({children, sticky, ...rest}: TableHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isSelectable} = React.useContext(SelectableContext);

    return (
      <TableHead sticky={sticky} ref={forwardedRef}>
        <tr {...rest}>
          {/* Add new column for checkbox to be displayed properly in the tbody */}
          {isSelectable && <th />}
          {children}
        </tr>
      </TableHead>
    );
  }
);

export {TableHeader};
