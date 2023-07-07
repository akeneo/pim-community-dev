import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {TableContext} from '../TableContext';

const TableHead = styled.thead<{sticky?: number} & AkeneoThemedProps>`
  ${({sticky}) =>
    undefined !== sticky &&
    css`
      th {
        position: sticky;
        top: ${sticky}px;
        z-index: 1;
        background-color: ${getColor('white')};
      }
    `}
`;

const SelectColumn = styled.th`
  width: 40px;
`;
const HasWarningColumn = styled.th`
  width: 20px;
  background: linear-gradient(to top, ${getColor('grey', 120)} 1px, ${getColor('white')} 0px);
`;
const OrderColumn = styled.th`
  width: 40px;
  background: linear-gradient(to top, ${getColor('grey', 120)} 1px, ${getColor('white')} 0px);
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

const TableHeader: React.FC<TableHeaderProps & {ref?: React.Ref<HTMLTableSectionElement>}> = React.forwardRef<HTMLTableSectionElement, TableHeaderProps>(
  ({children, sticky, ...rest}: TableHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isSelectable, isDragAndDroppable, hasWarningRows} = React.useContext(TableContext);

    return (
      <TableHead sticky={sticky} ref={forwardedRef}>
        <tr {...rest}>
          {/* Add new column for checkbox to be displayed properly in the tbody */}
          {isSelectable && <SelectColumn />}
          {hasWarningRows && <HasWarningColumn />}
          {isDragAndDroppable && <OrderColumn />}
          {children}
        </tr>
      </TableHead>
    );
  }
);

export {TableHeader};
export type {TableHeaderProps};
