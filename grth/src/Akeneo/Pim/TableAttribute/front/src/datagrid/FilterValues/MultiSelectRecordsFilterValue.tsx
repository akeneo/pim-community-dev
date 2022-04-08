import React, {useState} from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MultiSelectInput, useDebounce} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {castReferenceEntityColumnDefinition, RecordCode, ReferenceEntityRecord} from '../../models';
import {useAttributeContext} from '../../contexts';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {useRecords} from '../../product/useRecords';
import {useFetchRecords} from './useFetchRecords';

interface RecordOption extends ReferenceEntityRecord {
  hidden?: boolean;
}

const MultiSelectRecordsFilterValue: TableFilterValueRenderer = ({value, onChange, columnCode}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {attribute} = useAttributeContext();
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue, 200);
  const column = attribute?.table_configuration.find(columnDefinition => columnDefinition.code === columnCode);
  const referenceEntityCode = column ? castReferenceEntityColumnDefinition(column).reference_entity_identifier : '';

  const {items, handleNextPage} = useRecords({
    referenceEntityCode,
    isVisible: !!attribute,
    searchValue: debouncedSearchValue,
  });

  /**
   * Hidden values are used to display record labels
   * When a record is selected as value, but is not in "items" array, its label is not rendered anymore
   * To fix that behavior we add "cached records" inside "items" but as "hidden" options to have access to the label
   */
  const hiddenValues = React.useMemo(() => {
    const recordOptions: RecordOption[] = [];
    if (!value) return recordOptions;
    (value as RecordCode[])?.map(recordCode => {
      const record = ReferenceEntityRecordRepository.getCachedByCode(referenceEntityCode, recordCode);
      const isAlreadyInItems = items?.find(item => item.code === record?.code);

      if (record && !isAlreadyInItems) recordOptions.push({...record, hidden: true});
    });
    return recordOptions;
  }, [items, referenceEntityCode, value]);

  const allItems: RecordOption[] = [...(items || []), ...hiddenValues];

  return (
    <MultiSelectInput
      value={(value as string[] | undefined) || []}
      openLabel={translate('pim_common.open')}
      emptyResultLabel={translate('pim_common.no_result')}
      removeLabel={translate('pim_common.remove')}
      onChange={onChange}
      onSearchChange={setSearchValue}
      placeholder={translate('pim_table_attribute.datagrid.select_your_value')}
      onNextPage={handleNextPage}>
      {allItems?.map(option => (
        <MultiSelectInput.Option value={option.code} key={option.code} hidden={option.hidden} title={option.code}>
          {getLabel(option.labels, catalogLocale, option.code)}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

const useValueRenderer: FilteredValueRenderer = (value, columnCode) => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const records = useFetchRecords(value, columnCode);

  if (!Array.isArray(value)) {
    return null;
  }

  return records
    ? (value as RecordCode[])
        .map(recordCode => {
          const record = records.find(({code}) => code === recordCode);
          return getLabel(record?.labels || {}, catalogLocale, recordCode);
        })
        .join(', ')
    : null;
};

export {useValueRenderer};
export default MultiSelectRecordsFilterValue;
