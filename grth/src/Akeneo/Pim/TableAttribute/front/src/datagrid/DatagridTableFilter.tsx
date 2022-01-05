import React, {useEffect, useState} from 'react';
import {Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import {
  AttributeCode,
  BackendTableFilterValue,
  ColumnDefinition,
  isFilterValid,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  RecordCode,
  ReferenceEntityColumnDefinition,
  ReferenceEntityRecord,
  SelectOption,
  TableAttribute,
} from '../models';
import {AttributeFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {FilterSelectorList} from './FilterSelectorList';
import {useFetchOptions} from '../product';
import {useIsMounted} from '../shared';
import {AttributeContext} from '../contexts';
import {DatagridTableCriteria} from './DatagridTableCriteria';
import {ReferenceEntityRecordRepository} from '../repositories';
import {
  FilterBox,
  FilterButtonContainer,
  FilterContainer,
  FilterSectionTitle,
  FilterSectionTitleTitle,
} from '../shared/DatagridTableFilterStyle';

type DatagridTableFilterProps = {
  showLabel: boolean;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: AttributeCode;
  onChange: (value: BackendTableFilterValue) => void;
  initialDataFilter: PendingBackendTableFilterValue;
};

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  initialDataFilter,
  ...rest
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [filterValue, setFilterValue] = useState<PendingTableFilterValue | undefined>();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const isMounted = useIsMounted();
  const [records, setRecords] = useState<ReferenceEntityRecord[] | undefined>();

  const firstColumnType = attribute?.table_configuration[0]?.data_type;

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      if (isMounted()) {
        const tableAttribute = attribute as TableAttribute;
        setAttribute(tableAttribute);
      }
    });
  }, []);
  const optionsFromFirstSelectColumn = attribute
    ? getOptionsFromColumnCode(attribute.table_configuration[0].code)
    : undefined;

  useEffect(() => {
    if (!attribute || firstColumnType === 'select') return;

    const firstColumn = attribute?.table_configuration[0] as ReferenceEntityColumnDefinition;

    let codes: RecordCode[] = [];
    if (initialDataFilter.row && firstColumnType === 'reference_entity') codes.push(initialDataFilter.row);
    const filteredColumn = attribute?.table_configuration.find(({code}) => code === initialDataFilter.column);
    if (Array.isArray(initialDataFilter.value) && filteredColumn?.data_type === 'reference_entity')
      codes = codes.concat(initialDataFilter.value);

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
      !isMounted() ||
      (firstColumnType === 'reference_entity' && typeof records === 'undefined') ||
      (firstColumnType === 'select' && typeof optionsFromFirstSelectColumn === 'undefined')
    ) {
      return;
    }

    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.column);
    const optionsOrRecords =
      firstColumnType === 'select' ? (optionsFromFirstSelectColumn as SelectOption[]) : records || [];
    const row =
      optionsOrRecords.find(({code}) => code === initialDataFilter.row) ||
      (initialDataFilter.operator ? null : undefined);
    const pendingFilter = {
      row,
      column,
      value: initialDataFilter.value,
      operator: initialDataFilter.operator,
    };
    setFilterValue(pendingFilter);
  }, [optionsFromFirstSelectColumn, attribute, isMounted, records, firstColumnType]);

  const handleValidate = () => {
    if (filterValue && isFilterValid(filterValue)) {
      close();
      onChange({
        row: filterValue.row?.code,
        column: (filterValue.column as ColumnDefinition).code,
        operator: filterValue.operator,
        value: filterValue.value,
      });
    }
  };

  const handleClose = () => {
    if (!filterValue || !isFilterValid(filterValue)) {
      close();
      onChange({});
      setFilterValue({});
    } else {
      handleValidate();
    }
  };

  return (
    <AttributeContext.Provider value={{attribute, setAttribute}}>
      <Dropdown {...rest}>
        {isOpen && attribute && filterValue && (
          <Dropdown.Overlay onClose={handleClose}>
            <FilterContainer>
              <FilterSectionTitle title={getLabel(attribute.labels, catalogLocale, attribute.code)}>
                <FilterSectionTitleTitle>
                  {getLabel(attribute.labels, catalogLocale, attribute.code)}
                </FilterSectionTitleTitle>
              </FilterSectionTitle>
              <FilterSelectorList onChange={setFilterValue} initialFilter={filterValue} />
              <FilterButtonContainer>
                <Button onClick={handleValidate} disabled={!isFilterValid(filterValue)}>
                  {translate('pim_common.update')}
                </Button>
              </FilterButtonContainer>
            </FilterContainer>
          </Dropdown.Overlay>
        )}
        <FilterBox className='AknFilterBox-filter' onClick={open}>
          {showLabel && attribute && (
            <span className='AknFilterBox-filterLabel'>
              {getLabel(attribute.labels, catalogLocale, attribute.code)}
            </span>
          )}
          <DatagridTableCriteria filterValue={filterValue} />
          <span className='AknFilterBox-filterCaret' />
        </FilterBox>
        {canDisable && (
          <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable} />
        )}
      </Dropdown>
    </AttributeContext.Provider>
  );
};

export {DatagridTableFilter};
