export type TreeModel = {
  value: string;
  label: string;
  children?: TreeModel[];
  loading?: boolean;
  selected?: boolean;
  readOnly?: boolean;
};
