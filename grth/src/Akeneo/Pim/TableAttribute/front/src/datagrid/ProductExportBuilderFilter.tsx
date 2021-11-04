import React from 'react';
import {
  ColumnCode,
  FilterOperator,
  FilterValue,
  isFilterValid,
  PendingTableFilterValue,
  SelectOptionCode,
  TableAttribute,
} from '../models';
import {FilterSelectorList} from './FilterSelectorList';
import {FilterValuesMapping} from './FilterValues';
import styled from 'styled-components';
import {useFetchOptions} from '../product';
import {useAttributeContext} from '../contexts/AttributeContext';

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
  filterValuesMapping: FilterValuesMapping;
  onChange: (val: BackendTableProductExportFilterValue) => void;
  initialDataFilter: PendingTableProductExportFilterValue;
};

const FieldContainer = styled.div`
  margin-bottom: 0;
`;

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  filterValuesMapping,
  onChange,
  initialDataFilter,
}) => {
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
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
  const optionsForFirstColumn = attribute ? getOptionsFromColumnCode(attribute.table_configuration[0].code) : [];

  React.useEffect(() => {
    if (!attribute) {
      return;
    }

    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.value?.column);

    if (typeof optionsForFirstColumn === 'undefined') {
      return;
    }

    const row = optionsForFirstColumn.find(option => option.code === initialDataFilter.value?.row);
    setInitialFilter({
      row,
      column,
      value: initialDataFilter.value?.value,
      operator: initialDataFilter.operator,
    });
  }, [attribute, optionsForFirstColumn]);

  return (
    <FieldContainer className='AknFieldContainer AknFieldContainer--big'>
      <div className='AknFieldContainer-inputContainer'>
        {initialFilter && (
          <FilterSelectorList
            attribute={attribute as TableAttribute}
            filterValuesMapping={filterValuesMapping}
            onChange={handleChange}
            initialFilter={initialFilter}
            inline={true}
          />
        )}
      </div>
    </FieldContainer>
  );
};

export {ProductExportBuilderFilter};
