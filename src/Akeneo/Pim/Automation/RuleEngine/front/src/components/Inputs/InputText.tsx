import React from 'react';
import { Input, InputProps } from './Input';
import { InputTextCharacterCount } from './InputTextCharacterCount';

type Props = InputProps & {
  withCharactersLeft?: boolean;
  errors?: React.ReactNode;
};

const InputText = React.forwardRef<HTMLInputElement, Props>(
  (props, forwardedRef: React.Ref<HTMLInputElement>) => {
    const { withCharactersLeft, errors, className, ...remainingProps } = props;

    return (
      <>
        <Input
          className={className ?? 'AknTextField'}
          type='text'
          ref={forwardedRef}
          {...remainingProps}
        />
        {withCharactersLeft && props.name && props.maxLength && (
          <InputTextCharacterCount
            formName={props.name}
            maxLength={props.maxLength}
          />
        )}
        {errors}
        {withCharactersLeft && props.name && props.maxLength && (
          <div className='clearfix' />
        )}
      </>
    );
  }
);

InputText.displayName = 'InputText';

export { InputText };
