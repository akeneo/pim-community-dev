import {ColumnCode, TableAttribute} from '../../models';

type DatagridTableFilterValueProps = {
  value: any;
  onChange: (value: any) => void;
  attribute: TableAttribute;
  columnCode: ColumnCode;
};

export type TableFilterValueRenderer = React.FC<DatagridTableFilterValueProps>;
export type FilteredValueRenderer = (attributeCode: string) => (value: any, columnCode: ColumnCode) => string;

export type FilterValuesMapping = {
  [data_type: string]: {
    [operator: string]: {
      default: TableFilterValueRenderer;
      useValueRenderer: FilteredValueRenderer;
    };
  };
};
