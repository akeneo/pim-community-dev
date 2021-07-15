import {TableInput} from "akeneo-design-system";
import React from "react";
import {TextColumnValidation} from "../../models/TableConfiguration";

type TableInputTextProps = {
  value?: string;
  onChange: (value: string) => void;
  searchValue: string;
  validations: TextColumnValidation;
  inError?: boolean;
};

const TableInputText: React.FC<TableInputTextProps> = ({
  value,
  onChange,
  searchValue = '',
  validations,
  inError = false,
}) => {
  const highlighted = searchValue.indexOf(`${value}`) >= 0;
  const isTooLong = typeof validations.max_length !== 'undefined' && (value || '').length > validations.max_length;

  return <TableInput.Text
    value={value || ''}
    onChange={onChange}
    highlighted={highlighted}
    maxLength={validations.max_length}
    inError={inError || isTooLong}
  />
}

export {TableInputText};
