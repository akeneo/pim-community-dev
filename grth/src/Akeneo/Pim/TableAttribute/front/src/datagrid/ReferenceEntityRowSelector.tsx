import React from 'react';
import {ReferenceEntityColumnDefinition, ReferenceEntityRecord} from '../models';
import {useDebounce} from 'akeneo-design-system';
import {useAttributeContext} from '../contexts';
import {useRecords} from '../product/useRecords';
import {RowSelectorSelectInput} from './RowSelectorSelectInput';

type ReferenceEntityRowSelectorProps = {
  onChange: (record?: ReferenceEntityRecord | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a RecordCode: the user has selected a row
   */
  value?: ReferenceEntityRecord | null;
};

const ReferenceEntityRowSelector: React.FC<ReferenceEntityRowSelectorProps> = ({onChange, value}) => {
  const {attribute} = useAttributeContext();
  const [searchValue, setSearchValue] = React.useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue, 200);

  const {items, handleNextPage} = useRecords({
    referenceEntityCode: (attribute?.table_configuration[0] as ReferenceEntityColumnDefinition)
      .reference_entity_identifier,
    isVisible: true,
    searchValue: debouncedSearchValue,
  });

  return (
    <RowSelectorSelectInput<ReferenceEntityRecord>
      onChange={onChange}
      onNextPage={handleNextPage}
      options={items || []}
      setSearchValue={setSearchValue}
      value={value}
    />
  );
};

export {ReferenceEntityRowSelector};
