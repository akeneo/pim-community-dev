import React from 'react';
import {Input, InputProps} from './Input';

type InputDateType = 'date' | 'datetime-local';

type InputDateProps = {
  type?: InputDateType;
} & InputProps;

const InputDate = React.forwardRef<HTMLInputElement, InputDateProps>(
  ({type = 'date', ...rest}, forwardedRef: React.Ref<HTMLInputElement>) => {
    return (
      <Input
        className='AknTextField'
        type={type}
        ref={forwardedRef}
        {...rest}
      />
    );
  }
);

InputDate.displayName = 'InputDate';

export {InputDate, InputDateType};
