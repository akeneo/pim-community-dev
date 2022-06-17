type BooleanReplacementValues = {
  true: string[];
  false: string[];
};

const isBooleanReplacementValues = (values: any): values is BooleanReplacementValues =>
  typeof values === 'object' &&
  values !== null &&
  'true' in values &&
  Array.isArray(values.true) &&
  'false' in values &&
  Array.isArray(values.false);

export {isBooleanReplacementValues};
export type {BooleanReplacementValues};
