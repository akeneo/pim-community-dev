import React from 'react';
import {RecordColumnDefinition, ReferenceEntityRecord, SelectOption} from '../models';
import {useDebounce} from 'akeneo-design-system';
import {useAttributeContext} from '../contexts';
import {useRecords} from '../product/useRecords';
import {RowSelectorSelectInput} from './RowSelectorSelectInput';
import {LabelCollection} from '@akeneo-pim-community/shared';

type ReferenceEntityRowSelectorProps = {
  onChange: (option?: ReferenceEntityRecord | SelectOption | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a RecordCode: the user has selected a row
   */
  value?: ReferenceEntityRecord | null;
  anyRowOption: {code: string; labels: LabelCollection};
};

const ReferenceEntityRowSelector: React.FC<ReferenceEntityRowSelectorProps> = ({onChange, value, anyRowOption}) => {
  const {attribute} = useAttributeContext();
  const [searchValue, setSearchValue] = React.useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue, 200);

  const {items, handleNextPage} = useRecords({
    referenceEntityCode: (attribute?.table_configuration[0] as RecordColumnDefinition).reference_entity_identifier,
    isVisible: true,
    searchValue: debouncedSearchValue,
  });

  const filteredOptions = React.useMemo(() => [anyRowOption].concat(items || []), [anyRowOption, items]);

  return (
    <RowSelectorSelectInput
      onChange={onChange}
      onNextPage={handleNextPage}
      options={filteredOptions}
      setSearchValue={setSearchValue}
      value={value}
    />
  );
};

export {ReferenceEntityRowSelector};
