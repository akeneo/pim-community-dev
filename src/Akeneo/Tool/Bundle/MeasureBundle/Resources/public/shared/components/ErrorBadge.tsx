import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

const ErrorBadge = styled.div.attrs(() => ({role: 'alert'}))`
  width: 10px;
  height: 10px;
  background-color: ${getColor('red', 100)};
  border-radius: 50%;
  margin-left: 10px;
`;

export {ErrorBadge};
