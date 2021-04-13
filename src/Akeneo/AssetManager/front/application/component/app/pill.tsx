import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

const Pill = styled.div`
  background-color: ${getColor('yellow', 100)};
  width: 8px;
  min-width: 8px; // to fix a glitch on chrome when the pill is smashed
  height: 8px;
  border-radius: 8px;
  margin: 0 6px;
  align-self: center;
`;

export {Pill};
