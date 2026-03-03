import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';

const NoDataSection = styled.div`
  text-align: center;
  margin-top: 70px;
`;

const NoDataTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  text-align: center;
  margin: 30px 0 20px 0;
`;

const NoDataText = styled.div`
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('bigger')};
  text-align: center;
`;

export {NoDataSection, NoDataTitle, NoDataText};
