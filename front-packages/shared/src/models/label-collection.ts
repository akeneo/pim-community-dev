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

const getLabel = (labels: LabelCollection, locale: string, fallback: string): string =>
  labels && labels[locale] ? labels[locale] : `[${fallback}]`;

export {getLabel, isLabelCollection};
export type {LabelCollection};
