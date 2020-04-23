import styled from 'styled-components';
import { CoreButton } from './CoreButton';

const GreyGhostButton = styled(CoreButton)`
  color: ${({ theme }) => theme.color.grey100};
  border-color: ${({ theme }) => theme.color.grey80};
  background-color: ${({ theme }) => theme.color.white};
  &:hover {
    border-color: ${({ theme }) => theme.color.grey60};
  }
  &:active {
    border-color: ${({ theme }) => theme.color.grey60};
  }
  &:disabled {
    border-color: ${({ theme }) => theme.color.grey60};
    color: ${({ theme }) => theme.color.grey80};
  }
`;
const RedGhostButton = styled(CoreButton)`
  color: ${({ theme }) => theme.color.red100};
  border-color: ${({ theme }) => theme.color.red80};
  background-color: ${({ theme }) => theme.color.white};
  &:hover {
    border-color: ${({ theme }) => theme.color.red60};
  }
  &:active {
    border-color: ${({ theme }) => theme.color.red60};
  }
  &:disabled {
    border-color: ${({ theme }) => theme.color.red60};
    color: ${({ theme }) => theme.color.red80};
  }
`;

export { GreyGhostButton, RedGhostButton };
