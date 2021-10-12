import React from 'react';
import {TableAttribute} from '../models';
import {
  BackendTableFilterValue,
  FilterSelectorList,
  PendingBackendTableFilterValue,
  PendingTableFilterValue,
} from './FilterSelectorList';
import {FilterValuesMapping} from './FilterValues';
import {SelectOptionFetcher} from '../fetchers';
import {useRouter} from '@akeneo-pim-community/shared';
import styled from "styled-components";

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (val: BackendTableFilterValue) => void;
  initialDataFilter: PendingBackendTableFilterValue;
};

const FieldContainer = styled.div`
  margin-bottom: 0;
`

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  initialDataFilter,
}) => {
  const router = useRouter();
  const handleChange = (filter: PendingTableFilterValue) => {
    if (
      typeof filter.operator !== 'undefined' &&
      typeof filter.column !== 'undefined'
    ) {
      onChange({
        operator: filter.operator,
        column: filter.column?.code,
        value: filter.value,
        row: filter.row?.code,
      });
    }
  };

  const [initialFilter, setInitialFilter] = React.useState<PendingTableFilterValue | undefined>();

  React.useEffect(() => {
    const column = attribute.table_configuration.find(column => column.code === initialDataFilter.column);
    SelectOptionFetcher.fetchFromColumn(router, attribute.code, attribute.table_configuration[0].code).then(options => {
      const row = (options || []).find(option => option.code === initialDataFilter.row);
      setInitialFilter({
        row,
        column,
        value: initialDataFilter.value,
        operator: initialDataFilter.operator,
      });
    });
  }, []);

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
