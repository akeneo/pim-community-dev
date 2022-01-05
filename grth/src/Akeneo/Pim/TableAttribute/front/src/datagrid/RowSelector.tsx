import React from 'react';
import {ReferenceEntityRecord, SelectOption} from '../models';
import {useAttributeContext} from '../contexts';
import {SelectRowSelector} from './SelectRowSelector';
import {ReferenceEntityRowSelector} from './ReferenceEntityRowSelector';

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

const RowSelector: React.FC<RowSelectorProps> = ({onChange, value}) => {
  const {attribute} = useAttributeContext();

  return attribute?.table_configuration[0].data_type === 'select' ? (
    <SelectRowSelector onChange={onChange} value={value as SelectOption} />
  ) : (
    <ReferenceEntityRowSelector onChange={onChange} value={value as ReferenceEntityRecord} />
  );
};

export {RowSelector};
