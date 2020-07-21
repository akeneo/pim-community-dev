import React from 'react';
import { InputProps } from "../../../../../components/Inputs/Input";
import { InputNumber } from "../../../../../components/Inputs";

const PriceValue = React.forwardRef<HTMLInputElement, InputProps & { currencyCode: string }>(
  ({ currencyCode, ...props }, forwardedRef: React.Ref<HTMLInputElement>) => {
    return (
      <>
        <InputNumber
          className='AknTextField AknTextField--noRightRadius'
          type='number'
          ref={forwardedRef}
          step='any'
          {...props}
        />
        <span className='AknPriceList-currency'>{currencyCode}</span>
      </>
    );
  }
);

PriceValue.displayName = 'PriceValue';

export { PriceValue };

