import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

//TODO RAC-413 replace this with DSM Helper
export const HelperSection = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.blue10};
  display: flex;
  padding: 5px 20px;
  min-height: 80px;
  margin-bottom: 20px;
  width: 100%;
  line-height: 25px;
`;

export const HelperSeparator = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.grey80};
  width: 1px;
  margin: 10px 20px 10px 20px;
`;

export const HelperTitle = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.bigger};
  margin: 20px 0 20px 0;
`;

export const HelperText = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
`;
