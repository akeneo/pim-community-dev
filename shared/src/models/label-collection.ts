type LabelCollection = {
  [localeCode: string]: string;
};
const isLabelCollection = (labelCollection: any): labelCollection is LabelCollection => {
  if (undefined === labelCollection || typeof labelCollection !== 'object') {
    return false;
  }

  return !Object.keys(labelCollection).some(
    (key: string) => typeof key !== 'string' || typeof labelCollection[key] !== 'string'
  );
};

export {isLabelCollection};
export type {LabelCollection};
