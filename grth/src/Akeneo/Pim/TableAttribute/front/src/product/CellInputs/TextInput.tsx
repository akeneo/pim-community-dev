import {TableInput} from 'akeneo-design-system';
import React from 'react';
import {TextColumnValidation} from '../../models';
import {CellInput} from './index';

export const TABLE_STRING_MAX_LENGTH = 100;

type TableInputTextProps = {
  value?: string;
  onChange: (value: string) => void;
  validations: TextColumnValidation;
  inError?: boolean;
  readOnly?: boolean;
  highlighted?: boolean;
};

const TextInputInner: React.FC<TableInputTextProps> = ({
  value,
  onChange,
  highlighted = false,
  validations,
  inError = false,
  readOnly = false,
  ...rest
}) => {
  const maxLength = typeof validations.max_length !== 'undefined' ? validations.max_length : TABLE_STRING_MAX_LENGTH;
  const isTooLong = (value || '').length > maxLength;

  return (
    <TableInput.Text
      value={value || ''}
      onChange={onChange}
      highlighted={highlighted}
      maxLength={maxLength}
      inError={inError || isTooLong}
      readOnly={readOnly}
      {...rest}
    />
  );
};

const TextInput: CellInput = ({row, columnDefinition, onChange, inError, highlighted, ...rest}) => {
  const cell = row[columnDefinition.code] as string | undefined;

  return (
    <TextInputInner
      highlighted={highlighted}
      value={cell}
      onChange={onChange}
      validations={columnDefinition.validations as TextColumnValidation}
      inError={inError}
      {...rest}
    />
  );
};

export default TextInput;
