type ReplacementValues = {[from: string]: string};

type ReplacementOperation = {
  type: 'replacement';
  mapping: ReplacementValues;
};

const isReplacementOperation = (operation?: any): operation is ReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  'replacement' === operation.type &&
  'mapping' in operation &&
  'object' === typeof operation.mapping;

const getDefaultReplacementOperation = (): ReplacementOperation => ({
  type: 'replacement',
  mapping: {},
});

const isDefaultReplacementOperation = (operation?: ReplacementOperation): boolean =>
  operation?.type === 'replacement' && 0 === Object.keys(operation.mapping).length;

const filterEmptyValues = (values: ReplacementValues): ReplacementValues =>
  Object.entries(values)
    .filter(([_, value]) => '' !== value)
    .reduce((acc, [key, value]) => ({...acc, [key]: value}), {});

export {getDefaultReplacementOperation, filterEmptyValues, isReplacementOperation, isDefaultReplacementOperation};
export type {ReplacementOperation, ReplacementValues};
