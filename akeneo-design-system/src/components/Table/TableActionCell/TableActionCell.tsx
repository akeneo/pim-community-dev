import styled from 'styled-components';
import {TableCell, TableCellProps} from '../TableCell/TableCell';
import React, {ReactNode, Ref, SyntheticEvent} from 'react';
import {Button, ButtonProps} from '../../';

const TableActionCellContainer = styled(TableCell)`
  > div {
    opacity: 0;
  }
`;

type ActionCellProps = {
  /**
   * Content of the cell
   */
  children?: ReactNode;
} & TableCellProps;

const TableActionCell = React.forwardRef<HTMLTableCellElement, ActionCellProps>(
  ({children, ...rest}: ActionCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    const decoratedChildren = React.Children.map(children, child => {
      if (!React.isValidElement<ButtonProps>(child) || child.type !== Button) return child;

      return React.cloneElement(child, {
        onClick: (e: SyntheticEvent) => {
          e.stopPropagation();
          child.props.onClick && child.props.onClick(e);
        },
      });
    });

    return (
      <TableActionCellContainer ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </TableActionCellContainer>
    );
  }
);

export {TableActionCell};
