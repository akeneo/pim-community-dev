import React from 'react';
import {TableAttribute} from "../models";
import {FilterSelectorList} from "./FilterSelectorList";
import {FilterValuesMapping} from "./FilterValues";
import {DatagridTableFilterValue, TableFilterValue} from "./DatagridTableFilter";
import {SelectOptionFetcher} from "../fetchers";
import {useRouter} from '@akeneo-pim-community/shared';

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  filterValuesMapping: FilterValuesMapping;
  onChange: (val: DatagridTableFilterValue) => void;
  initialDataFilter: DatagridTableFilterValue;
}

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  filterValuesMapping,
  onChange,
  initialDataFilter,
}) => {
  const router = useRouter();
  const handleChange = (val: TableFilterValue) => {
    onChange({
      operator: val.operator as string,
      column: val.column?.code as string,
      value: val.value,
      row: val.row?.code,
    });
  };

  const [initialFilter, setInitialFilter] = React.useState<TableFilterValue | undefined>();

  React.useEffect(() => {
    const column = attribute.table_configuration.find((column => column.code === initialDataFilter.column));
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

  return <div className="AknFieldContainer AknFieldContainer--big">
    <div className="AknFieldContainer-inputContainer">
      {initialFilter &&
      <FilterSelectorList
        attribute={attribute}
        filterValuesMapping={filterValuesMapping}
        onChange={handleChange}
        initialFilter={initialFilter}
        inline={true}
      />
      }
    </div>
  </div>;

}

export {ProductExportBuilderFilter};
