import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type LabelProps = {small?: boolean, grey?: boolean};

export const Label = styled.div<LabelProps>`
  color: ${(props: ThemedProps<LabelProps>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<LabelProps>) => props.theme.fontSize.default};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;

  ::first-letter {
    text-transform: capitalize
  }
`
