import React, {useEffect} from 'react';
import {FilterSelectorList} from '../datagrid';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContext, LocaleCodeContext} from '../contexts';
import {PendingBackendTableFilterValue, PendingTableFilterValue, TableAttribute} from '../models';
import {AttributeContext} from '../contexts';
import {
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  ReferenceEntityRecord,
  TableAttribute,
} from '../models';
import {useFetchOptions} from '../product';
import {ReferenceEntityRecordRepository} from '../repositories';
import {useUserContext} from '@akeneo-pim-community/shared';

type TableAttributeConditionLineProps = {
  attribute?: TableAttribute;
  value: PendingBackendTableFilterValue;
  onChange: (value: PendingBackendTableFilterValue) => void;
};

const TableAttributeConditionLineInput: React.FC<TableAttributeConditionLineProps> = props => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  return (
    <DependenciesProvider>
      <LocaleCodeContext.Provider value={{localeCode: catalogLocale}}>
        <InnerTableAttributeConditionLine {...props} />
      </LocaleCodeContext.Provider>
    </DependenciesProvider>
  );
};

const InnerTableAttributeConditionLine: React.FC<TableAttributeConditionLineProps> = ({attribute, value, onChange}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute | undefined>(attribute);
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttributeState);
  const [records, setRecords] = React.useState<ReferenceEntityRecord[] | undefined>();
  const firstColumn = attributeState?.table_configuration[0];
  const router = useRouter();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  const selectOptionsFromColumnCode = getOptionsFromColumnCode(firstColumn?.code || '');

  useEffect(() => {
    if (!attribute) return;
    if (firstColumn?.data_type !== 'reference_entity') return;
    ReferenceEntityRecordRepository.search(router, firstColumn.reference_entity_identifier, {
      locale: catalogLocale,
      channel: userContext.get('catalogScope'),
    }).then(records => {
      setRecords(records);
    });
  }, [attribute, catalogLocale, router, userContext]);

  const optionsOrRecords = React.useMemo(
    () => (firstColumn?.data_type === 'reference_entity' ? records : selectOptionsFromColumnCode),
    [records, selectOptionsFromColumnCode]
  );

  if (
    !attributeState ||
    (firstColumn?.data_type === 'select' && !selectOptionsFromColumnCode) ||
    (firstColumn?.data_type === 'reference_entity' && !records)
  ) {
    return <></>;
  }

  const initialFilter = {
    ...value,
    column: attributeState.table_configuration.find(column => column.code === value.column),
    row: optionsOrRecords?.find(({code}) => code === value.row),
  };

  const handleChange = (value: PendingTableFilterValue) => {
    onChange({
      ...value,
      column: value.column?.code,
      row: value.row?.code,
    });
  };

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FilterSelectorList initialFilter={initialFilter} inline onChange={handleChange} />
    </AttributeContext.Provider>
  );
};

export {TableAttributeConditionLineInput};
