import {FilterValuesMapping} from '../datagrid';

const FilterValuesProvider: {
  getMapping: () => FilterValuesMapping;
} = {
  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  getMapping: () => __moduleConfig.filter_values as FilterValuesMapping,
};

export {FilterValuesProvider};
