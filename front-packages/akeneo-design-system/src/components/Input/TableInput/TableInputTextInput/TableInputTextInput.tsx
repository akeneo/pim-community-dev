import { TableInputRow } from "../TableInputRow/TableInputRow";
import styled from "styled-components";
import { TextInput } from "../../TextInput/TextInput";

const TableInputTextInput = styled(TextInput)`
  height: 36px;
  border-width: 0;
  background: none;
`;

TableInputRow.displayName = 'TableInput.TextInput';

export {TableInputTextInput};
