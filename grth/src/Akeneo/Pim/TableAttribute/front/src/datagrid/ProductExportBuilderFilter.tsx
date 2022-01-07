import React, {useEffect, useState} from 'react';
import {
  ColumnCode,
  FilterOperator,
  FilterValue,
  isFilterValid,
  PendingTableFilterValue,
  RecordCode,
  ReferenceEntityColumnDefinition,
  ReferenceEntityRecord,
  SelectOption,
  SelectOptionCode,
  TableAttribute,
} from '../models';
import {FilterSelectorList} from './FilterSelectorList';
import styled from 'styled-components';
import {useFetchOptions} from '../product';
import {AttributeContext} from '../contexts';
import {ReferenceEntityRecordRepository} from '../repositories';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';

export type BackendTableProductExportFilterValue = {
  operator: FilterOperator;
  value: {
    row?: SelectOptionCode;
    column: ColumnCode;
    value: FilterValue;
  };
};

export type PendingTableProductExportFilterValue = {
  operator?: FilterOperator;
  value?: {
    row?: SelectOptionCode;
    column?: ColumnCode;
    value?: FilterValue;
  };
};

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  onChange: (val: BackendTableProductExportFilterValue) => void;
  initialDataFilter: PendingTableProductExportFilterValue;
};

const FieldContainer = styled.div`
  margin-bottom: 0;
`;

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  onChange,
  initialDataFilter,
}) => {
  const router = useRouter();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [attributeState, setAttributeState] = React.useState<TableAttribute>(attribute);
  const {getOptionsFromColumnCode} = useFetchOptions(attributeState, setAttributeState);
  const firstColumnType = attribute.table_configuration[0].data_type;
  const [records, setRecords] = useState<ReferenceEntityRecord[] | undefined>();

  const handleChange = (filter: PendingTableFilterValue) => {
    if (isFilterValid(filter)) {
      onChange({
        operator: filter.operator as FilterOperator,
        value: {
          column: filter.column?.code as ColumnCode,
          value: filter.value as FilterValue,
          row: filter.row?.code,
        },
      });
    }
  };

  const [initialFilter, setInitialFilter] = React.useState<PendingTableFilterValue | undefined>();
  const optionsFromFirstSelectColumn = attributeState
    ? getOptionsFromColumnCode(attributeState.table_configuration[0].code)
    : [];

  useEffect(() => {
    if (!attribute || firstColumnType === 'select') return;

    const firstColumn = attribute?.table_configuration[0] as ReferenceEntityColumnDefinition;

    const codes: RecordCode[] = [];
    if (initialDataFilter.value?.row && firstColumnType === 'reference_entity') codes.push(initialDataFilter.value.row);

    ReferenceEntityRecordRepository.search(router, firstColumn.reference_entity_identifier, {
      locale: catalogLocale,
      channel: userContext.get('catalogScope'),
      codes,
    }).then(records => {
      setRecords(records);
    });
  }, [attribute, catalogLocale, router, userContext]);

  React.useEffect(() => {
    if (
      !attribute ||
      (firstColumnType === 'reference_entity' && typeof records === 'undefined') ||
      (firstColumnType === 'select' && typeof optionsFromFirstSelectColumn === 'undefined')
    ) {
      return;
    }

    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.value?.column);
    const optionsOrRecords =
      firstColumnType === 'select' ? (optionsFromFirstSelectColumn as SelectOption[]) : records || [];
    const row =
      optionsOrRecords.find(({code}) => code === initialDataFilter.value?.row) ||
      (initialDataFilter.operator ? null : undefined);
    const pendingFilter = {
      row,
      column,
      value: initialDataFilter.value?.value,
      operator: initialDataFilter.operator,
    };
    setInitialFilter(pendingFilter);
  }, [optionsFromFirstSelectColumn, attribute, records, firstColumnType]);

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <FieldContainer className='AknFieldContainer AknFieldContainer--big'>
        <div className='AknFieldContainer-inputContainer'>
          {initialFilter && <FilterSelectorList onChange={handleChange} initialFilter={initialFilter} inline={true} />}
        </div>
      </FieldContainer>
    </AttributeContext.Provider>
  );
};

export {ProductExportBuilderFilter};
