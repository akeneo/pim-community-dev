import {TableInput} from 'akeneo-design-system';
import React from 'react';
import {TextColumnValidation} from '../../models/TableConfiguration';

type TableInputTextProps = {
  value?: string;
  onChange: (value: string) => void;
  validations: TextColumnValidation;
  inError?: boolean;
  readOnly?: boolean;
  highlighted?: boolean;
};

const TableInputText: React.FC<TableInputTextProps> = ({
  value,
  onChange,
  highlighted = false,
  validations,
  inError = false,
  readOnly = false,
  ...rest
}) => {
  const isTooLong = typeof validations.max_length !== 'undefined' && (value || '').length > validations.max_length;

  return (
    <TableInput.Text
      value={value || ''}
      onChange={onChange}
      highlighted={highlighted}
      maxLength={validations.max_length}
      inError={inError || isTooLong}
      readOnly={readOnly}
      {...rest}
    />
  );
};

export {TableInputText};
