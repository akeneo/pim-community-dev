import React from 'react';
import {
  BackendTableFilterValue,
  ColumnCode,
  isFilterValid,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
  TableAttribute,
} from '../models';
import {FilterSelectorList} from './FilterSelectorList';
import {FilterValuesMapping} from './FilterValues';
import styled from 'styled-components';
import {useFetchOptions} from '../product';

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (val: BackendTableFilterValue) => void;
  initialDataFilter: PendingBackendTableFilterValue;
};

const FieldContainer = styled.div`
  margin-bottom: 0;
`;

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  initialDataFilter,
}) => {
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const handleChange = (filter: PendingTableFilterValue) => {
    if (isFilterValid(filter)) {
      onChange({
        operator: filter.operator as string,
        column: filter.column?.code as ColumnCode,
        value: filter.value,
        row: filter.row?.code,
      });
    }
  };

  const [initialFilter, setInitialFilter] = React.useState<PendingTableFilterValue | undefined>();
  const optionsForFirstColumn = getOptionsFromColumnCode(attribute.table_configuration[0].code);

  React.useEffect(() => {
    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.column);

    if (typeof optionsForFirstColumn === 'undefined') {
      return;
    }
    const row = optionsForFirstColumn.find(option => option.code === initialDataFilter.row);
    setInitialFilter({
      row,
      column,
      value: initialDataFilter.value,
      operator: initialDataFilter.operator,
    });
  }, [optionsForFirstColumn]);

  return (
    <FieldContainer className='AknFieldContainer AknFieldContainer--big'>
      <div className='AknFieldContainer-inputContainer'>
        {initialFilter && (
          <FilterSelectorList
            attribute={attribute}
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
