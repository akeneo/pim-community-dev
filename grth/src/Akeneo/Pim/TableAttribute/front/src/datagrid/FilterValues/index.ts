import {ColumnCode, FilterValue} from '../../models';

type DatagridTableFilterValueProps = {
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  columnCode: ColumnCode;
};

export type TableFilterValueRenderer = React.FC<DatagridTableFilterValueProps>;
export type FilteredValueRenderer = () => (value: FilterValue, columnCode: ColumnCode) => string;

export type FilterValuesMapping = {
  [data_type: string]: {
    [operator: string]: {
      default: TableFilterValueRenderer;
      useValueRenderer: FilteredValueRenderer;
    };
  };
};
