import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type ActionButtonProps = {};

export const ActionButton = styled.div<ActionButtonProps>`
  height: 12px;
  min-width: 12px;
  line-height: 10px;
  font-size: ${(props: ThemedProps<ActionButtonProps>) => props.theme.fontSize.default}px;
  color: ${(props: ThemedProps<ActionButtonProps>) => props.theme.color.purple100}
  border-radius: 100px;
  font-weight: normal;
  padding: 0 15px;
  vertical-align: middle;
  display: inline-block;
  cursor: pointer;
  white-space: nowrap;
  outline: none;
  text-transform: uppercase;
  transition: background 0.1s ease-in;
`;
