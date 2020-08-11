import styled from 'styled-components';

import {CoreButton} from '../CoreButton';

const PrimaryButton = styled(CoreButton)`
  color: red;
  background-color: #67b373;
  &:hover {
    background-color: #528f5c;
  }
  &:active {
    background-color: #3d6b45;
  }
  &:focus {
    border-color: blue;
  }
  &:disabled {
    background-color: #c2e1c7;
  }
`;

export {PrimaryButton};
