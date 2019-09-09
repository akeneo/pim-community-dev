import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';

export const NotificationSection = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.blue10};
  margin-top: 2px;
  height: 44px;
  witdht: 100%;
  display: flex;
  padding: 10px;
`;

export const NotificationText = styled.div`
  font-size: 13px;
  display: flex;
`;
