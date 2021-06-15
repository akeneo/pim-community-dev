import React, {ReactNode} from 'react';
import styled from 'styled-components';
import { Override } from '../../../shared';

//TODO be sure to select the appropriate container element here
const TableInputContainer = styled.div<{level: string}>``;
const TableInputCellContainer = styled.span`
  display: inline-block;
  width: 200px;
  height: 50px;
  border: 1px solid red;
`;
const TableInputRowContainer = styled.div`
  display: flex;
`;

type TableInputProps = Override<
  React.HTMLAttributes<HTMLTableElement>,
  {
    /**
     * TODO.
     */
    level?: 'primary' | 'warning' | 'danger';

    /**
     * TODO.
     */
    children?: ReactNode;
  }
>;

const TableInputBody: React.FC<{}> = ({children}) => {
  return <>
    {children}
  </>;
};

const TableInputRow: React.FC<{}> = ({children}) => {
  return <TableInputRowContainer>
    {children}
  </TableInputRowContainer>;
};

const TableInputCell: React.FC<{}> = ({children}) => {
  return <TableInputCellContainer>
    {children}
  </TableInputCellContainer>;
};

const TableInputHeaderCell: React.FC<{}> = ({children}) => {
  return <TableInputCellContainer>
    {children}
  </TableInputCellContainer>;
};

const TableInputHeader: React.FC<{}> = ({children}) => {
  return <TableInputRowContainer>
    {children}
  </TableInputRowContainer>;
};

/**
 * TODO.
 */
const TableInput = ({ level = 'primary', children, ...rest }: TableInputProps) => {
  return (
    <TableInputContainer level={level} {...rest}>
      {children}
    </TableInputContainer>
  );
};

TableInputHeader.displayName = 'TableInput.Header';
TableInputHeaderCell.displayName = 'TableInput.HeaderCell';
TableInputBody.displayName = 'TableInput.Body';
TableInputRow.displayName = 'TableInput.Row';
TableInputCell.displayName = 'TableInput.Cell';

TableInput.Header = TableInputHeader;
TableInput.HeaderCell = TableInputHeaderCell;
TableInput.Body = TableInputBody;
TableInput.Row = TableInputRow;
TableInput.Cell = TableInputCell;

export {TableInput};
