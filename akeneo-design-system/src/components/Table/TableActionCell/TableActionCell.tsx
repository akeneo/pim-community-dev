import styled from 'styled-components';
import {TableCell} from '../TableCell/TableCell';
import React, {ReactNode, Ref, SyntheticEvent} from 'react';
import {Button, ButtonProps, IconButton} from '../../';

const TableActionCellContainer = styled(TableCell)`
  // Only display buttons on row hover
  > div {
    opacity: 0;
  }
`;

type ActionCellProps = {
  /**
   * Multiple buttons
   */
  children?: ReactNode;
};

const TableActionCell = React.forwardRef<HTMLTableCellElement, ActionCellProps>(
  ({children, ...rest}: ActionCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    const decoratedChildren = React.Children.map(children, child => {
      if (React.isValidElement<ButtonProps>(child) && (child.type === Button || child.type === IconButton)) {
        return React.cloneElement(child, {
          onClick: (e: SyntheticEvent) => {
            e.stopPropagation();
            child.props.onClick && child.props.onClick(e);
          },
        });
      }

      return child;
    });

    return (
      <TableActionCellContainer ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </TableActionCellContainer>
    );
  }
);

export {TableActionCell};
