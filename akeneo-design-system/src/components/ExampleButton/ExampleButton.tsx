import React, { FunctionComponent } from "react";
import styled from "styled-components";

const getButtonBackground = (mode: string): string => {
    switch (mode) {
        case "DANGER":
            return "red";
        default:
            return "white";
    }
};

const getButtonColor = (mode: string): string => {
    switch (mode) {
        case "DANGER":
            return "white";
        default:
            return "black";
    }
};

type StyledButtonProps = {
    disabled: boolean,
    mode: string,
}

const StyledButton = styled.button<StyledButtonProps>`
  color: ${props => getButtonColor(props.mode)};
  background: ${props => getButtonBackground(props.mode)};
  font-size: 15px;
  height: 3rem;
  width: 10rem;
  border-radius: 3%;
  &:hover {
    cursor: ${props => (props.disabled ? "not-allowed" : "pointer")};
  }
`;

type ExampleButtonProps = {
    children: any,
    disabled?: boolean,
    mode?: string,
    onClick: () => {},
}

const ExampleButton: FunctionComponent<ExampleButtonProps> = ({ children, disabled = false, mode = "PRIMARY", onClick }) => {
    return (
        <StyledButton disabled={disabled} onClick={onClick} mode={mode}>
            {children}
        </StyledButton>
    );
};

export { ExampleButton };
