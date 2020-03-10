import styled from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

export const Helper = styled.div`
  background-color: ${akeneoTheme.color.blue10};
  display: flex;
  align-items: center;
  padding: 20px;
  min-height: 80px;
  margin-bottom: 20px;
  width: 100%;
  line-height: 25px;
`;

export const HelperTitle = styled.div`
  color: ${akeneoTheme.color.grey140};
  font-size: ${akeneoTheme.fontSize.bigger};
  font-weight: 600;
  margin-left: 20px;
  border-left: 1px solid ${akeneoTheme.color.grey80};
  padding-left: 20px;
`;

export const HelperText = styled.div`
  color: ${akeneoTheme.color.grey120};
  font-size: ${akeneoTheme.fontSize.default};
  font-weight: 400;
`;
