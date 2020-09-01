import React, { forwardRef } from 'react';
import { Label } from '../Labels';
import styled from 'styled-components';

const InputElement = styled.input<{ small?: boolean }>`
  width: ${({ small }) => (small ? '95px' : '100%')};
`;

type Props = {
  ariaInvalid?: boolean;
  ariaDescribedBy?: string;
  label?: string;
  hiddenLabel?: boolean;
  hasError?: boolean;
  small?: boolean;
} & React.InputHTMLAttributes<HTMLInputElement>;

const Input = forwardRef<HTMLInputElement, Props>(
  (
    { ariaInvalid, ariaDescribedBy, children, id, label, hiddenLabel, ...rest },
    forwardedRef: React.Ref<HTMLInputElement>
  ) => {
    return (
      <>
        {!children && label ? (
          <Label
            className='AknFieldContainer-label control-label'
            hiddenLabel={hiddenLabel}
            htmlFor={id}
            label={label}
          />
        ) : (
          children
        )}
        <InputElement
          aria-invalid={ariaInvalid}
          aria-describedby={ariaDescribedBy}
          id={id}
          ref={forwardedRef}
          {...rest}
        />
      </>
    );
  }
);

Input.displayName = 'Input';

export { Input, Props as InputProps };
