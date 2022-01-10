import React from 'react';
import {RecordCode, SelectOptionCode} from '../models';
import {useAttributeContext} from '../contexts';
import {SelectRowSelector} from './SelectRowSelector';
import {ReferenceEntityRowSelector} from './ReferenceEntityRowSelector';

type RowSelectorProps = {
  onChange: (option?: RecordCode | SelectOptionCode | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a SelectOption: the user has selected a row
   */
  value?: RecordCode | SelectOptionCode | null;
};

const RowSelector: React.FC<RowSelectorProps> = ({onChange, value}) => {
  const {attribute} = useAttributeContext();

  return attribute?.table_configuration[0].data_type === 'select' ? (
    <SelectRowSelector onChange={onChange} value={value as SelectOptionCode} />
  ) : (
    <ReferenceEntityRowSelector onChange={onChange} value={value as RecordCode} />
  );
};

export {RowSelector};
