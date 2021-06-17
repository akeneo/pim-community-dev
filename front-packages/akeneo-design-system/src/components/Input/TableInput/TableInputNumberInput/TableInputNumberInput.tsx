import { TableInputRow } from "../TableInputRow/TableInputRow";
import styled from "styled-components";
import { NumberInput } from "../../NumberInput/NumberInput";

const TableInputNumberInput = styled(NumberInput)`
  height: 36px;
  border-width: 0;
  background: none;
  padding-left: 10px;
  padding-right: 10px;
`;

TableInputRow.displayName = 'TableInput.NumberInput';

export {TableInputNumberInput};
