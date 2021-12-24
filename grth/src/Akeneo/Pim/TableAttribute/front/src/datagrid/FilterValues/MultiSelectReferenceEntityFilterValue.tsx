import React, {useState} from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MultiSelectInput, useDebounce} from 'akeneo-design-system';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {RecordCode, RecordColumnDefinition, ReferenceEntityRecord} from '../../models';
import {useAttributeContext} from '../../contexts';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {useRecords} from '../../product/useRecords';

interface RecordOption extends ReferenceEntityRecord {
  hidden?: boolean;
}

const MultiSelectReferenceEntityFilterValue: TableFilterValueRenderer = ({value, onChange, columnCode}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useRouter();
  const catalogLocale = userContext.get('catalogLocale');
  const {attribute} = useAttributeContext();
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue, 200);

  const referenceEntityCode =
    (
      attribute?.table_configuration.find(
        columnDefinition => columnDefinition.code === columnCode
      ) as RecordColumnDefinition
    )?.reference_entity_identifier || '';

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
    const tab: RecordOption[] = [];
    if (!value) return;
    (value as string[])?.map(recordCode => {
      const record = ReferenceEntityRecordRepository.getCachedByCode(referenceEntityCode, recordCode);
      const isAlreadyInItems = items?.find(item => item.code === record?.code);

      if (record && !isAlreadyInItems) tab.push({...record, hidden: true});
      else if (!record) {
        // fetch record if it is neither in cache nor inside items
        ReferenceEntityRecordRepository.findByCode(router, referenceEntityCode, recordCode).then(response => {
          if (response) tab.push({...response, hidden: true});
        });
      }
    });
    return tab;
  }, [items, referenceEntityCode, router, value]);

  // TODO : bug remaining => labels are not retrieved when value is stored inside hiddenValues and user is typing a search value
  const allItems: RecordOption[] = items && hiddenValues ? [...items, ...hiddenValues] : (items as RecordOption[]);

  return (
    <MultiSelectInput
      value={(value as string[] | undefined) || []}
      openLabel={translate('pim_common.open')}
      emptyResultLabel={translate('pim_common.no_result')}
      removeLabel={translate('pim_common.remove')}
      onChange={onChange}
      onSearchChange={setSearchValue}
      placeholder={translate('pim_table_attribute.datagrid.select_your_value')}
      onNextPage={handleNextPage}
    >
      {allItems?.map(option => (
        <MultiSelectInput.Option value={option.code} key={option.code} hidden={option.hidden}>
          {getLabel(option.labels, catalogLocale, option.code)}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

const useValueRenderer: FilteredValueRenderer = () => {
  const {attribute} = useAttributeContext();
  const userContext = useUserContext();

  return (value, columnCode) => {
    const catalogLocale = userContext.get('catalogLocale');
    const referenceEntityIdentifier = (
      attribute?.table_configuration.find(item => item.code === columnCode) as RecordColumnDefinition
    )?.reference_entity_identifier;

    return ((value as string[] | undefined) || [])
      .map((subValue: RecordCode) => {
        const record = ReferenceEntityRecordRepository.getCachedByCode(referenceEntityIdentifier, subValue);
        return getLabel(record?.labels || {}, catalogLocale, record?.code || subValue);
      })
      .join(', ');
  };
};

export {useValueRenderer};
export default MultiSelectReferenceEntityFilterValue;
