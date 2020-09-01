import styled from 'styled-components';
import { CoreButton } from './CoreButton';

const GreyGhostButton = styled(CoreButton)`
  color: ${({ theme }): string => theme.color.grey100};
  border-color: ${({ theme }): string => theme.color.grey80};
  background-color: ${({ theme }): string => theme.color.white};
  &:hover {
    border-color: ${({ theme }): string => theme.color.grey60};
  }
  &:active {
    border-color: ${({ theme }): string => theme.color.grey60};
  }
  &:disabled {
    border-color: ${({ theme }): string => theme.color.grey60};
    color: ${({ theme }): string => theme.color.grey80};
  }
`;
const RedGhostButton = styled(CoreButton)`
  color: ${({ theme }): string => theme.color.red100};
  border-color: ${({ theme }): string => theme.color.red80};
  background-color: ${({ theme }): string => theme.color.white};
  &:hover {
    border-color: ${({ theme }): string => theme.color.red60};
  }
  &:active {
    border-color: ${({ theme }): string => theme.color.red60};
  }
  &:disabled {
    border-color: ${({ theme }): string => theme.color.red60};
    color: ${({ theme }): string => theme.color.red80};
  }
`;
const BlueGhostButton = styled(CoreButton)`
  color: ${({ theme }): string => theme.color.blue100};
  border-color: ${({ theme }): string => theme.color.blue100};
  background-color: ${({ theme }): string => theme.color.white};
  &:hover {
    color: ${({ theme }): string => theme.color.blue140};
    border-color: ${({ theme }): string => theme.color.blue60};
  }
  &:active {
    color: ${({ theme }): string => theme.color.blue140};
    border-color: ${({ theme }): string => theme.color.blue140};
  }
  &:disabled {
    border-color: ${({ theme }): string => theme.color.blue60};
    color: ${({ theme }): string => theme.color.blue60};
  }
`;

export { GreyGhostButton, RedGhostButton, BlueGhostButton };
