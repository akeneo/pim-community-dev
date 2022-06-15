type BooleanReplacementValues = {
  true: string[];
  false: string[];
  null: string[];
};

const isBooleanReplacementValues = (values: any): values is BooleanReplacementValues =>
  typeof values === 'object' &&
  values !== null &&
  'true' in values &&
  Array.isArray(values.true) &&
  'false' in values &&
  Array.isArray(values.false) &&
  'null' in values &&
  Array.isArray(values.null);

export {isBooleanReplacementValues};
export type {BooleanReplacementValues};
