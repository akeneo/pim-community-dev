import React, {ReactNode} from 'react';
import styled from 'styled-components';
import { Override } from '../../../shared';
import { TableInputHeader } from "./TableInputHeader/TableInputHeader";
import { TableInputHeaderCell } from "./TableInputHeaderCell/TableInputHeaderCell";
import { TableInputBody } from "./TableInputBody/TableInputBody";
import { TableInputCell } from "./TableInputCell/TableInputCell";
import { TableInputRow } from "./TableInputRow/TableInputRow";
import { TableInputTextInput } from "./TableInputTextInput/TableInputTextInput";
import { TableInputNumberInput } from "./TableInputNumberInput/TableInputNumberInput";

const TableInputContainer = styled.table`
  border-spacing: 0 4px;
  width: 100%;
`;

type TableInputProps = Override<
  React.HTMLAttributes<HTMLTableElement>,
  {
    /**
     * TODO.
     */
    children?: ReactNode;
  }
>;

/**
 * TODO.
 */
const TableInput = ({ children, ...rest }: TableInputProps) => {
  return (
    <TableInputContainer {...rest}>
      {children}
    </TableInputContainer>
  );
};

TableInput.Header = TableInputHeader;
TableInput.HeaderCell = TableInputHeaderCell;
TableInput.Body = TableInputBody;
TableInput.Row = TableInputRow;
TableInput.Cell = TableInputCell;
TableInput.TextInput = TableInputTextInput;
TableInput.NumberInput = TableInputNumberInput;

export {TableInput};
