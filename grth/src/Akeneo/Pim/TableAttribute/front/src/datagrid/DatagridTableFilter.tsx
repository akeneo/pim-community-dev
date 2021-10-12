import React, {useEffect, useState} from 'react';
import {Button, Dropdown, getColor, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {
  BackendTableFilterValue,
  ColumnCode,
  ColumnDefinition,
  isFilterValid,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  TableAttribute,
} from '../models';
import {AttributeFetcher, SelectOptionFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {FilterValuesMapping} from './FilterValues';
import styled from 'styled-components';
import {FilterSelectorList} from './FilterSelectorList';

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
  label: string;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: string;
  onChange: (value: BackendTableFilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
  initialDataFilter: PendingBackendTableFilterValue;
};

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  label,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  filterValuesMapping,
  initialDataFilter,
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [filterValue, setFilterValue] = useState<PendingTableFilterValue>({});
  const [initialFilter, setInitialFilter] = React.useState<PendingTableFilterValue | undefined>();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      const tableAttribute = attribute as TableAttribute;
      setAttribute(tableAttribute);

      const column = tableAttribute.table_configuration.find(column => column.code === initialDataFilter.column);
      SelectOptionFetcher.fetchFromColumn(router, attribute.code, tableAttribute.table_configuration[0].code).then(options => {
        const row = (options || []).find(option => option.code === initialDataFilter.row);
        const pendingFilter = {
          row,
          column,
          value: initialDataFilter.value,
          operator: initialDataFilter.operator,
        };
        setFilterValue(pendingFilter);
        setInitialFilter(pendingFilter);
      });
    });
  }, []);

  const valueRenderers: {[dataType: string]: {[operator: string]: (value: any, columnCode: ColumnCode) => string}} = {};
  Object.keys(filterValuesMapping).forEach(dataType => {
    valueRenderers[dataType] = {};
    Object.keys(filterValuesMapping[dataType]).forEach(operator => {
      valueRenderers[dataType][operator] = filterValuesMapping[dataType][operator].useValueRenderer(attributeCode);
    });
  });

  const handleValidate = () => {
    if (isFilterValid(filterValue)) {
      close();
      onChange({
        row: filterValue.row?.code,
        column: (filterValue.column as ColumnDefinition).code,
        operator: filterValue.operator as string,
        value: filterValue.value,
      });
    }
  };

  let criteriaHint = translate('pim_common.all');
  if (isFilterValid(filterValue) && attribute) {
    criteriaHint = '';
    criteriaHint +=
      typeof filterValue.row === 'undefined'
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
    <Dropdown>
      {isOpen && attribute && initialFilter && (
        <Dropdown.Overlay onClose={close}>
          <FilterContainer>
            <FilterSectionTitle title={label}>
              <FilterSectionTitleTitle>{label}</FilterSectionTitleTitle>
            </FilterSectionTitle>
            <FilterSelectorList
              attribute={attribute}
              filterValuesMapping={filterValuesMapping}
              onChange={setFilterValue}
              initialFilter={initialFilter}
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
        {showLabel && <span className='AknFilterBox-filterLabel'>{label}</span>}
        <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaHint}>
          {criteriaHint}
        </span>
        <span className='AknFilterBox-filterCaret' />
      </FilterBox>
      {canDisable && (
        <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable} />
      )}
    </Dropdown>
  );
};

export {DatagridTableFilter};
