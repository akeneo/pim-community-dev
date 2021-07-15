import {TableInput} from "akeneo-design-system";
import React from "react";
import {NumberColumnValidation} from "../../models/TableConfiguration";

type TableInputNumberProps = {
  value?: string;
  onChange: (value?: number) => void;
  searchValue?: string;
  validations: NumberColumnValidation;
  inError?: boolean;
}

const TableInputNumber: React.FC<TableInputNumberProps> = ({
  value,
  onChange,
  searchValue = '',
  validations,
  inError= false,
  ...rest
}) => {
  const highlighted = typeof value !== 'undefined' && searchValue.indexOf(value) >= 0;
  const isLessThanMin = typeof value !== 'undefined' && typeof validations.min !== 'undefined' && parseFloat(value) < validations.min;
  const isGreaterThanMax = typeof value !== 'undefined' && typeof validations.max !== 'undefined' && parseFloat(value) > validations.max;
  const isFloatButNoDecimalsAllowed = typeof value !== 'undefined' && !validations.decimals_allowed && (parseFloat(value) % 1) !== 0;

  return <TableInput.Number
    value={value}
    onChange={(value: string) => {onChange(parseFloat(value))}}
    highlighted={highlighted}
    min={validations.min}
    max={validations.max}
    inError={inError || isLessThanMin || isGreaterThanMax || isFloatButNoDecimalsAllowed}
    {...rest}
  />
}

export {TableInputNumber};
