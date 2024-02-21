import styled from 'styled-components';
import React, {ReactNode, Ref, SyntheticEvent} from 'react';
import {Button, ButtonProps, IconButton} from '../../';
import {getColor} from '../../../theme';
import {Override} from '../../../shared';

const TableActionCellContainer = styled.td`
  color: ${getColor('grey', 140)};
  border-bottom: 1px solid ${getColor('grey', 60)};
  padding: 0 10px;
  width: 50px;
`;

const InnerTableActionCellContainer = styled.div`
  opacity: 0;
  display: flex;
  gap: 10px;
`;

type ActionCellProps = Override<
  React.TdHTMLAttributes<HTMLTableCellElement>,
  {
    /**
     * Multiple buttons
     */
    children?: ReactNode;
  }
>;

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
        <InnerTableActionCellContainer>{decoratedChildren}</InnerTableActionCellContainer>
      </TableActionCellContainer>
    );
  }
);

export {TableActionCell};
