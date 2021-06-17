import { TableInputRow } from "../TableInputRow/TableInputRow";
import styled from "styled-components";
import { TextInput } from "../../TextInput/TextInput";

const TableInputTextInput = styled(TextInput)`
  height: 36px;
  border-width: 0;
  background: none;
  padding-left: 10px;
  padding-right: 10px;
  margin-left: 1px;
`;

TableInputRow.displayName = 'TableInput.TextInput';

export {TableInputTextInput};
