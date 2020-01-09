import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

export const Link = styled.a`
  font-weight: 400;
  text-decoration: underline;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  cursor: pointer;
`;
