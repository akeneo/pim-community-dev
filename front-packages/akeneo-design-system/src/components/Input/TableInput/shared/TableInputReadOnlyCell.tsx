import styled from 'styled-components';
import {getColor} from '../../../../theme';

const TableInputReadOnlyCell = styled.div`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: not-allowed;
  padding: 0 10px;
  color: ${getColor('grey', 100)};
  height: 39px;
  line-height: 39px;
  display: flex;
  align-items: center;
  justify-content: space-between;
`;

export {TableInputReadOnlyCell};
