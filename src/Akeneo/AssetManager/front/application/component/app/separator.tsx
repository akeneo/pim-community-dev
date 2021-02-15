import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

export const Separator = styled.div`
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};
  margin: 0 10px;
  height: 20px;

  &:first-child,
  &:last-child {
    margin: 0;
  }
`;

export default Separator;
