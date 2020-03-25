import styled from "styled-components";

import { CoreButton } from "./CoreButton";

const PrimaryButton = styled(CoreButton)`
  color: ${({ theme }) => theme.color.white};
  background-color: ${({ theme }) => theme.color.green100};
  border-color: ${({ theme }) => theme.color.green100};
  &:hover {
    background-color: ${({ theme }) => theme.color.green120};
  }
  &:active {
    background-color: ${({ theme }) => theme.color.green140};
  }
  &:focus {
    border-color: blue;
  }
  &:disabled {
    background-color: ${({ theme }) => theme.color.green40};
  }
`;

PrimaryButton.displayName = "PrimaryButton";

export { PrimaryButton };
