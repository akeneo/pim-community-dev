import styled from 'styled-components';

import {CoreButton} from './CoreButton';

const PrimaryButton = styled(CoreButton)`
  color: ${({theme}): string => theme.color.white};
  background-color: ${({theme}): string => theme.color.green100};
  border-width: 0;
  &:hover {
    background-color: ${({theme}): string => theme.color.green120};
  }
  &:active {
    background-color: ${({theme}): string => theme.color.green140};
  }
  &:focus {
    border-color: blue;
  }
  &:disabled {
    border-color: ${({theme}): string => theme.color.green40};
    background-color: ${({theme}): string => theme.color.green40};
  }
`;

PrimaryButton.displayName = 'PrimaryButton';

export {PrimaryButton};
