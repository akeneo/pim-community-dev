import React from 'react';
import {TableInputRow} from '../TableInputRow/TableInputRow';
import styled from 'styled-components';
import {TextInput} from '../../TextInput/TextInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputReadOnlyCell} from '../shared/TableInputReadOnlyCell';
import {Override} from '../../../../shared';
import {InputProps} from '../../common/InputProps';
import {highlightCell} from '../shared/highlightCell';

const EditableTableInputText = styled(TextInput)<{highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
  height: 39px;
  padding-left: 10px;
  padding-right: 10px;
  border-radius: 0;
  border: none;
  background: none;

  ${highlightCell};

  &:focus {
    box-shadow: 0 0 0 1px ${getColor('grey', 100)};
  }
`;

type TableInputTextProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  {
    highlighted?: boolean;
    inError?: boolean;
  }
>;

const TableInputText = ({children, value, ...rest}: TableInputTextProps) => {
  const {readOnly} = React.useContext(TableInputContext);
  if (readOnly) {
    return <TableInputReadOnlyCell title={value}>{value}</TableInputReadOnlyCell>;
  } else
    return (
      <EditableTableInputText value={value} {...rest}>
        {children}
      </EditableTableInputText>
    );
};

TableInputRow.displayName = 'TableInput.TextInput';

export {TableInputText};
