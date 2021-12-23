import React, {useEffect, useState} from 'react';
import {Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import {
  AttributeCode,
  BackendTableFilterValue,
  ColumnDefinition,
  isFilterValid,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  ReferenceEntityRecord,
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
  const [referenceEntities, setReferenceEntities] = useState<ReferenceEntityRecord[] | null | undefined>();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      if (isMounted()) {
        const tableAttribute = attribute as TableAttribute;
        setAttribute(tableAttribute);
      }
    });
  }, []);

  useEffect(() => {
    const firstColumn = attribute?.table_configuration[0];
    if (firstColumn?.data_type !== 'record') return;

    ReferenceEntityRecordRepository.search(router, firstColumn.reference_entity_identifier, {
      locale: catalogLocale,
      channel: userContext.get('catalogScope'),
    }).then(records => {
      setReferenceEntities(records);
    });
  }, [attribute?.table_configuration, router]);

  const optionsForFirstColumn = React.useMemo(() => {
    if (!attribute) return undefined;
    const firstColumn = attribute.table_configuration[0];
    if (firstColumn.data_type === 'record') {
      return referenceEntities;
    }
    return getOptionsFromColumnCode(attribute.table_configuration[0].code);
  }, [attribute, getOptionsFromColumnCode, referenceEntities]);

  React.useEffect(() => {
    if (!attribute || !isMounted() || typeof optionsForFirstColumn === 'undefined' || optionsForFirstColumn === null)
      return;

    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.column);
    const row =
      optionsForFirstColumn.find(option => option.code === initialDataFilter.row) ||
      (initialDataFilter.operator ? null : undefined);
    const pendingFilter = {
      row,
      column,
      value: initialDataFilter.value,
      operator: initialDataFilter.operator,
    };
    setFilterValue(pendingFilter);
  }, [optionsForFirstColumn, attribute]);

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
