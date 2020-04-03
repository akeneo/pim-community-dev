import React from "react";
import styled from "styled-components";

type Props = {
  label: string;
} & React.InputHTMLAttributes<HTMLInputElement>;

const StyledLabel = styled.label`
  padding: 10px 0;
`;

const InputText = React.forwardRef<HTMLInputElement, Props>(
  function InputTextWithLabel(
    { id, label, value, readOnly, disabled, onChange },
    forwardedRef: React.Ref<HTMLInputElement>
  ) {
    return (
      <>
        <StyledLabel
          className="AknFieldContainer-label control-label"
          htmlFor={id}
        >
          {label}
        </StyledLabel>
        <div className="AknFieldContainer-inputContainer">
          <input
            className="AknTextField"
            id={id}
            onChange={onChange}
            ref={forwardedRef}
            type="text"
            value={value}
            readOnly={readOnly}
            disabled={disabled}
          />
        </div>
      </>
    );
  }
);

export { InputText };
