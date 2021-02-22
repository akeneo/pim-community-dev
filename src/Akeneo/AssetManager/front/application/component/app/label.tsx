import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type LabelProps = {color?: string, isCode?: boolean};

export const Label = styled.span<LabelProps>`
  color: ${(props: ThemedProps<LabelProps>) => (undefined === props.color ? props.theme.color.grey120 : props.color)};
  font-size: ${(props: ThemedProps<LabelProps>) => props.theme.fontSize.default};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;

   ::first-letter {
     text-transform: ${(props: ThemedProps<LabelProps>) => true === props.isCode ? 'initial' : 'capitalize'};
   }
`;
