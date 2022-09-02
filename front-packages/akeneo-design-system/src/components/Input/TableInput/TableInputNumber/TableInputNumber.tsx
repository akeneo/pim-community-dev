import React from 'react';
import {TableInputRow} from '../TableInputRow/TableInputRow';
import styled from 'styled-components';
import {NumberInput} from '../../NumberInput/NumberInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {InputProps} from '../../common/InputProps';
import {TableInputReadOnlyCell} from '../shared/TableInputReadOnlyCell';
import {TableInputContext} from '../TableInputContext';
import {highlightCell} from '../shared/highlightCell';

const EditableTableInputNumber = styled(NumberInput)<{highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
  height: 39px;
  padding-left: 10px;
  padding-right: 35px;
  border-radius: 0;
  border: none;
  background: none;

  ${highlightCell};

  &:focus {
    box-shadow: 0 0 0 1px ${getColor('grey', 100)};
  }
`;

type TableInputNumberProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  {
    highlighted?: boolean;
    inError?: boolean;
  }
>;

const TableInputNumber = ({children, value, ...rest}: TableInputNumberProps) => {
  const {readOnly} = React.useContext(TableInputContext);
  if (readOnly) {
    return <TableInputReadOnlyCell title={value}>{value}</TableInputReadOnlyCell>;
  } else
    return (
      <EditableTableInputNumber value={value} {...rest}>
        {children}
      </EditableTableInputNumber>
    );
};

TableInputRow.displayName = 'TableInput.NumberInput';

export {TableInputNumber};
