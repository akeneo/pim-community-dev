type FlattenAttribute = {
  code: string;
  labels: {[locale: string]: string};
};

type Attribute = {
  code: string;
  label: string;
};

export type {FlattenAttribute, Attribute};
