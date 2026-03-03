import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

const TooltipHeader = styled.span`
  color: ${getColor('blue', 120)};
  font-weight: 700;
`;

const TooltipContent = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
  line-height: 15px;
`;

export {TooltipHeader, TooltipContent};
