import React from "react";
import styled from "styled-components";

type Props = {
  ariaInvalid?: boolean;
  ariaDescribedBy?: string;
  label?: string;
} & React.InputHTMLAttributes<HTMLInputElement>;

const StyledLabel = styled.label`
  padding-bottom: 10px;
`;

const InputNumber = React.forwardRef<HTMLInputElement, Props>(
  function InputNumberWithLabel(
    { ariaInvalid, ariaDescribedBy, children, id, label, ...rest },
    forwardedRef: React.Ref<HTMLInputElement>
  ) {
    return (
      <>
        {!children ? (
          <StyledLabel
            className="AknFieldContainer-label control-label"
            htmlFor={id}
          >
            {label}
          </StyledLabel>
        ) : (
          children
        )}
        <input
          aria-invalid={ariaInvalid}
          aria-describedby={ariaDescribedBy}
          className="AknTextField"
          id={id}
          ref={forwardedRef}
          type="number"
          {...rest}
        />
      </>
    );
  }
);

export { InputNumber, StyledLabel };
