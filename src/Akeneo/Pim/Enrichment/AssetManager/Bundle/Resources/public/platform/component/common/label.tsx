import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';

export const Label = styled.div`
  color: ${(props: ThemedProps<{small: boolean, grey: boolean}>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<{small: boolean, grey: boolean}>) => props.theme.fontSize.default};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;

  ::first-letter {
    text-transform: capitalize
  }
`
