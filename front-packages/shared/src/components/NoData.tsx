import styled, {StyledComponent, ThemeProps} from 'styled-components';
import {getColor, getFontSize, Theme} from 'akeneo-design-system';

const NoDataSection = styled.div`
  text-align: center;
  margin-top: 70px;
`;

const NoDataTitle: StyledComponent<"div", any, Record<string, unknown> & ThemeProps<Theme>, never> = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  text-align: center;
  margin: 30px 0 20px 0;
`;

const NoDataText: StyledComponent<"div", any, Record<string, unknown> & ThemeProps<Theme>, never> = styled.div`
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('bigger')};
  text-align: center;
`;

export {NoDataSection, NoDataTitle, NoDataText};
