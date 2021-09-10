import {TableInput} from 'akeneo-design-system';
import React from 'react';
import {CellInput} from './index';
import {useTranslate} from '@akeneo-pim-community/shared/lib/hooks/useTranslate';

const BooleanInput: CellInput = ({row, columnDefinition, onChange, inError, highlighted, ...rest}) => {
  const translate = useTranslate();
  const cell = row[columnDefinition.code] as boolean | undefined;

  return (
    <TableInput.Boolean
      highlighted={highlighted}
      value={typeof cell === 'undefined' ? null : cell}
      onChange={(value: boolean | null) => onChange(null === value ? undefined : value)}
      yesLabel={translate('pim_common.yes')}
      noLabel={translate('pim_common.no')}
      clearLabel={translate('pim_common.clear')}
      openDropdownLabel={translate('pim_common.open')}
      inError={inError}
      {...rest}
    />
  );
};

export default BooleanInput;
