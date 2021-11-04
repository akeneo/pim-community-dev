import React, {useEffect, useState} from 'react';
import {Button, Dropdown, getColor, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {
  AttributeCode,
  BackendTableFilterValue,
  ColumnCode,
  ColumnDefinition,
  isFilterValid,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  TableAttribute,
} from '../models';
import {AttributeFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {FilterValuesMapping} from './FilterValues';
import styled from 'styled-components';
import {FilterSelectorList} from './FilterSelectorList';
import {useFetchOptions} from '../product';
import {useIsMounted} from '../shared';
import {useAttributeContext} from '../contexts/AttributeContext';

const FilterBox = styled.div`
  margin-bottom: 10px;
  width: 200px;
`;

const FilterSectionTitleTitle = styled(SectionTitle.Title)`
  color: ${getColor('brand', 100)};
`;
const FilterSectionTitle = styled(SectionTitle)`
  border-bottom-color: ${getColor('brand', 100)};
`;

const FilterContainer = styled.div`
  width: 280px;
  padding: 0 20px 10px;
`;

const FilterButtonContainer = styled.div`
  text-align: center;
`;

type DatagridTableFilterProps = {
  showLabel: boolean;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: AttributeCode;
  onChange: (value: BackendTableFilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
  initialDataFilter: PendingBackendTableFilterValue;
};

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  filterValuesMapping,
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

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      if (isMounted()) {
        const tableAttribute = attribute as TableAttribute;
        setAttribute(tableAttribute);
      }
    });
  }, []);

  const optionsForFirstColumn = attribute ? getOptionsFromColumnCode(attribute.table_configuration[0].code) : undefined;

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

  const valueRenderers: {[dataType: string]: {[operator: string]: (value: any, columnCode: ColumnCode) => string}} = {};
  Object.keys(filterValuesMapping).forEach(dataType => {
    valueRenderers[dataType] = {};
    Object.keys(filterValuesMapping[dataType]).forEach(operator => {
      valueRenderers[dataType][operator] = filterValuesMapping[dataType][operator].useValueRenderer(attributeCode);
    });
  });

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

  let criteriaHint = translate('pim_common.all');
  if (filterValue && isFilterValid(filterValue) && attribute) {
    criteriaHint = '';
    criteriaHint +=
      typeof filterValue.row === 'undefined' || filterValue.row === null
        ? translate('pim_table_attribute.datagrid.any') + ' '
        : getLabel(filterValue.row.labels, catalogLocale, filterValue.row.code) + ' ';
    criteriaHint +=
      getLabel(
        (filterValue.column as ColumnDefinition).labels,
        catalogLocale,
        (filterValue.column as ColumnDefinition).code
      ) + ' ';
    criteriaHint += translate(`pim_common.operators.${filterValue.operator}`) + ' ';

    const valueRenderer = (valueRenderers[(filterValue.column as ColumnDefinition).data_type || ''] || {})[
      filterValue.operator || ''
    ];
    if (valueRenderer) {
      criteriaHint += valueRenderer(filterValue.value, (filterValue.column as ColumnDefinition).code);
    }
  }

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
              <FilterSelectorList
                attribute={attribute}
                filterValuesMapping={filterValuesMapping}
                onChange={setFilterValue}
                initialFilter={filterValue}
              />
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
            <span className='AknFilterBox-filterLabel'>{getLabel(attribute.labels, catalogLocale, attribute.code)}</span>
          )}
          <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaHint}>
            {criteriaHint}
          </span>
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
