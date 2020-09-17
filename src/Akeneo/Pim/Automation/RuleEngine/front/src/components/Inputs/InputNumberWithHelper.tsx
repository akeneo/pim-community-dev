import React from 'react';
import { InputProps } from './Input';
import { InputNumber } from './index';

const InputNumberWithHelper = React.forwardRef<
  HTMLInputElement,
  InputProps & { helper: string; hasError?: boolean }
>(
  (
    { helper, hasError, ...props },
    forwardedRef: React.Ref<HTMLInputElement>
  ) => {
    return (
      <>
        <InputNumber
          className={`AknTextField AknNumberField--hideArrows AknTextField--noRightRadius${
            hasError ? ' AknTextField--error' : ''
          }`}
          type='number'
          ref={forwardedRef}
          step='any'
          {...props}
        />
        <span
          className={`AknPriceList-currency${
            hasError ? ' AknPriceList-currency--error' : ''
          }`}>
          {helper}
        </span>
      </>
    );
  }
);

InputNumberWithHelper.displayName = 'InputNumberWithHelper';

export { InputNumberWithHelper };
