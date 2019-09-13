import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

export const NotificationSection = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.blue10};
  margin-top: 2px;
  height: 44px;
  width: 100%;
  display: flex;
  padding: 10px;
`;

export const NotificationText = styled.div`
  font-size: 13px;
  display: flex;
`;
