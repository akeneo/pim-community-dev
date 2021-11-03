import {AttributeCode, ColumnCode, FilterValue, TableAttribute} from '../../models';

type DatagridTableFilterValueProps = {
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  attribute: TableAttribute;
  columnCode: ColumnCode;
};

export type TableFilterValueRenderer = React.FC<DatagridTableFilterValueProps>;
export type FilteredValueRenderer = (
  attributeCode: AttributeCode
) => (value: FilterValue, columnCode: ColumnCode) => string;

export type FilterValuesMapping = {
  [data_type: string]: {
    [operator: string]: {
      default: TableFilterValueRenderer;
      useValueRenderer: FilteredValueRenderer;
    };
  };
};
