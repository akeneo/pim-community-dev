export const isString = (value: any): value is string => {
  return null !== value && undefined !== value && typeof value === 'string';
};
export const isObject = (value: any): boolean => {
  return null !== value && undefined !== value && typeof value === 'object';
};
export const isBoolean = (value: any): value is boolean => {
  return null !== value && undefined !== value && typeof value === 'boolean';
};
export const isNumber = (value: any): value is number => {
  return null !== value && undefined !== value && typeof value === 'number';
};
export const isArray = (value: any): value is Array<any> => {
  return null !== value && undefined !== value && Array.isArray(value);
};
export const isLabels = (labels: any): boolean => {
  if (undefined === labels || typeof labels !== 'object') {
    return false;
  }

  if (Object.keys(labels).some((key: string) => typeof key !== 'string' || typeof labels[key] !== 'string')) {
    return false;
  }

  return true;
};
