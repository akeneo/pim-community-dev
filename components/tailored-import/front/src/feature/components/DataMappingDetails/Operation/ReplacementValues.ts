type ReplacementValues = {[from: string]: string[]};

const isReplacementValues = (values: any): values is ReplacementValues =>
  typeof values === 'object' &&
  values !== null &&
  Object.keys(values).every(key => typeof key === 'string' && Array.isArray(values[key]));

const filterEmptyValues = (values: ReplacementValues): ReplacementValues =>
  Object.entries(values)
    .filter(([_, values]) => 0 < values.length)
    .reduce((acc, [key, values]) => ({...acc, [key]: values}), {});

export {filterEmptyValues, isReplacementValues};
export type {ReplacementValues};
