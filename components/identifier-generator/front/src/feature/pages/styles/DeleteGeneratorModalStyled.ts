import styled from 'styled-components';
import {getFontSize} from 'akeneo-design-system';

const Message = styled.p`
  margin-top: 0;
  margin-bottom: 20px;
  font-size: ${getFontSize('big')};
`;
const StyledDelete = {
  Message,
};
export {StyledDelete};
