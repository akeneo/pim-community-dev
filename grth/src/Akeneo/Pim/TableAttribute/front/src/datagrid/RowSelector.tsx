import React from 'react';
import {ReferenceEntityRecord, SelectOption} from '../models';
import {useAttributeContext} from '../contexts';
import {SelectRowSelector} from './SelectRowSelector';
import {ReferenceEntityRowSelector} from './ReferenceEntityRowSelector';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';

type RowSelectorProps = {
  onChange: (option?: SelectOption | ReferenceEntityRecord | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a SelectOption: the user has selected a row
   */
  value?: SelectOption | ReferenceEntityRecord | null;
};

export const ANY_OPTION_CODE = '[any option]';

const RowSelector: React.FC<RowSelectorProps> = ({onChange, value}) => {
  const translate = useTranslate();
  const {attribute} = useAttributeContext();
  const catalogLocale = useUserContext().get('catalogLocale');

  const anyRowOption = {
    code: ANY_OPTION_CODE,
    labels: {
      [catalogLocale]: translate('pim_table_attribute.datagrid.any_row'),
    },
  };

  return attribute?.table_configuration[0].data_type === 'select' ? (
    <SelectRowSelector onChange={onChange} value={value as SelectOption} anyRowOption={anyRowOption} />
  ) : (
    <ReferenceEntityRowSelector
      onChange={onChange}
      value={value as ReferenceEntityRecord}
      anyRowOption={anyRowOption}
    />
  );
};

export {RowSelector};
