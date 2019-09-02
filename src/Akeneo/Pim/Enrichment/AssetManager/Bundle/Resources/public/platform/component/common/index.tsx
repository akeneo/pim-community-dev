import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';

export const Pill = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.yellow100}
  width: 8px;
  min-width: 8px; // to fix a glitch on chrome when the pill is smashed
  height: 8px;
  border-radius: 8px;
  margin: 0 6px;
  align-self: center;
`

export const Spacer = styled.div`
  flex: 1;
`;

export const Separator = styled.div`
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};
  margin: 0 10px;
  height: 24px;
`;
