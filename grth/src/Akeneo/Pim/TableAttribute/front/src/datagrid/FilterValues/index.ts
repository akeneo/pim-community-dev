import {ColumnCode, TableAttribute} from "../../models";

type DatagridTableFilterValueProps = {
  value: any;
  onChange: (value: any) => void;
  attribute: TableAttribute;
  columnCode: ColumnCode;
}

export type DatagridTableFilterValueRenderer = React.FC<DatagridTableFilterValueProps>;

export type FilterValuesMapping = {
  [data_type: string]: {
    [operator: string]: {
      default: DatagridTableFilterValueRenderer;
    }
  }
}
