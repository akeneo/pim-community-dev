import {TableInput} from 'akeneo-design-system';
import React from 'react';
import {NumberColumnValidation} from '../../models';
import {CellInput} from './index';

type TableInputNumberProps = {
  value?: string;
  onChange: (value?: string) => void;
  highlighted?: boolean;
  validations: NumberColumnValidation;
  inError?: boolean;
  readOnly?: boolean;
};

const InnerNumberInput: React.FC<TableInputNumberProps> = ({
  value,
  onChange,
  highlighted = false,
  validations,
  inError = false,
  readOnly = false,
  ...rest
}) => {
  const isLessThanMin =
    typeof value !== 'undefined' && typeof validations.min !== 'undefined' && parseFloat(value) < validations.min;
  const isGreaterThanMax =
    typeof value !== 'undefined' && typeof validations.max !== 'undefined' && parseFloat(value) > validations.max;
  const isFloatButNoDecimalsAllowed =
    typeof value !== 'undefined' && !validations.decimals_allowed && parseFloat(value) % 1 !== 0;

  return (
    <TableInput.Number
      value={value || ''}
      onChange={onChange}
      highlighted={highlighted}
      min={validations.min}
      max={validations.max}
      inError={inError || isLessThanMin || isGreaterThanMax || isFloatButNoDecimalsAllowed}
      readOnly={readOnly}
      {...rest}
    />
  );
};

const NumberInput: CellInput = ({row, columnDefinition, onChange, inError, highlighted, ...rest}) => {
  const cell = row[columnDefinition.code] as string | undefined;

  return (
    <InnerNumberInput
      highlighted={highlighted}
      value={cell}
      onChange={onChange}
      validations={columnDefinition.validations as NumberColumnValidation}
      inError={inError}
      {...rest}
    />
  );
};

export default NumberInput;
