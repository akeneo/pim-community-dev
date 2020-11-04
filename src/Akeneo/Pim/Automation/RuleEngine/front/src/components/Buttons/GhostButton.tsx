import styled from 'styled-components';
import {CoreButton} from './CoreButton';

const GreyGhostButton = styled(CoreButton)`
  color: ${({theme}): string => theme.color.grey120};
  border-color: ${({theme}): string => theme.color.grey100};
  background-color: ${({theme}): string => theme.color.white};
  &:hover {
    background-color: ${({theme}): string => theme.color.grey20};
  }
  &:active {
    border-color: ${({theme}): string => theme.color.grey140};
  }
  &:disabled {
    border-color: ${({theme}): string => theme.color.grey60};
    color: ${({theme}): string => theme.color.grey80};
  }
`;
const RedGhostButton = styled(CoreButton)`
  color: ${({theme}): string => theme.color.red100};
  border-color: ${({theme}): string => theme.color.red100};
  background-color: ${({theme}): string => theme.color.white};
  &:hover {
    color: ${({theme}): string => theme.color.red120};
  }
  &:active {
    border-color: ${({theme}): string => theme.color.red140};
    color: ${({theme}): string => theme.color.red140};
  }
  &:disabled {
    border-color: ${({theme}): string => theme.color.red40};
    color: ${({theme}): string => theme.color.red60};
  }
`;
const BlueGhostButton = styled(CoreButton)`
  color: ${({theme}): string => theme.color.blue100};
  border-color: ${({theme}): string => theme.color.blue100};
  background-color: ${({theme}): string => theme.color.white};
  &:hover {
    color: ${({theme}): string => theme.color.blue120};
  }
  &:active {
    color: ${({theme}): string => theme.color.blue140};
    border-color: ${({theme}): string => theme.color.blue140};
  }
  &:disabled {
    border-color: ${({theme}): string => theme.color.blue40};
    color: ${({theme}): string => theme.color.blue60};
  }
`;

export {GreyGhostButton, RedGhostButton, BlueGhostButton};
