import React from 'react';
import { Input, InputProps } from './Input';

const InputText = React.forwardRef<HTMLInputElement, InputProps>(
  (props, forwardedRef: React.Ref<HTMLInputElement>) => {
    return (
      <Input
        className='AknTextField'
        type='text'
        ref={forwardedRef}
        {...props}
      />
    );
  }
);

InputText.displayName = 'InputText';

export { InputText };
