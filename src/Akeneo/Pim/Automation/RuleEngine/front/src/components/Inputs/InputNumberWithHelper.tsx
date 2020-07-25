import React from 'react';
import { InputProps } from './Input';
import { InputNumber } from './index';

const InputNumberWithHelper = React.forwardRef<
  HTMLInputElement,
  InputProps & { helper: string }
>(({ helper, ...props }, forwardedRef: React.Ref<HTMLInputElement>) => {
  return (
    <>
      <InputNumber
        className='AknTextField AknTextField--noRightRadius'
        type='number'
        ref={forwardedRef}
        step='any'
        {...props}
      />
      <span className='AknPriceList-currency'>{helper}</span>
    </>
  );
});

InputNumberWithHelper.displayName = 'InputNumberWithHelper';

export { InputNumberWithHelper };
