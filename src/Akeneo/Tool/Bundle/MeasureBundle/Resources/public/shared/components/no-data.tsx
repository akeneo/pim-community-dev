import styled from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

export const NoDataSection = styled.div`
  text-align: center;
  margin-top: 70px;
`;

export const NoDataTitle = styled.div`
  color: ${akeneoTheme.color.grey140};
  font-size: ${akeneoTheme.fontSize.title};
  text-align: center;
  margin: 30px 0 20px 0;
`;

export const NoDataText = styled.div`
  color: ${akeneoTheme.color.grey120};
  font-size: ${akeneoTheme.fontSize.bigger};
  text-align: center;
`;
