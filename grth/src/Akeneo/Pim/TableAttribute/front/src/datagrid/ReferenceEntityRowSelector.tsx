import React from 'react';
import {castReferenceEntityColumnDefinition, RecordCode, ReferenceEntityRecord} from '../models';
import {useDebounce} from 'akeneo-design-system';
import {useAttributeContext} from '../contexts';
import {useRecords} from '../product/useRecords';
import {RowSelectorSelectInput} from './RowSelectorSelectInput';

type ReferenceEntityRowSelectorProps = {
  onChange: (record?: RecordCode | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a RecordCode: the user has selected a row
   */
  value?: RecordCode | null;
};

const ReferenceEntityRowSelector: React.FC<ReferenceEntityRowSelectorProps> = ({onChange, value}) => {
  const {attribute} = useAttributeContext();
  const [searchValue, setSearchValue] = React.useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue, 200);
  const firstColumn = attribute?.table_configuration[0];

  const {items, handleNextPage} = useRecords({
    referenceEntityCode: firstColumn
      ? castReferenceEntityColumnDefinition(firstColumn).reference_entity_identifier
      : undefined,
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
