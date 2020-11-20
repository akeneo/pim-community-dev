import styled from 'styled-components';
import {TableCell} from '../TableCell/TableCell';
import React, {ReactNode, SyntheticEvent} from 'react';
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
};

const TableActionCell = ({children, ...rest}: ActionCellProps) => {
  const decoratedChildren = React.Children.map(children, child => {
    if (!React.isValidElement<ButtonProps>(child) || child.type !== Button) return child;

    return React.cloneElement(child, {
      onClick: (e: SyntheticEvent) => {
        e.stopPropagation();
        child.props.onClick && child.props.onClick(e);
      },
    });
  });

  return <TableActionCellContainer {...rest}>{decoratedChildren}</TableActionCellContainer>;
};

export {TableActionCell};
