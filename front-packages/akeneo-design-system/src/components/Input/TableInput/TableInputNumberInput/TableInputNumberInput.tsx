import { TableInputRow } from "../TableInputRow/TableInputRow";
import styled from "styled-components";
import { NumberInput } from "../../NumberInput/NumberInput";

const TableInputNumberInput = styled(NumberInput)`
  height: 36px;
  border-width: 0;
  background: none;
`;

TableInputRow.displayName = 'TableInput.NumberInput';

export {TableInputNumberInput};
