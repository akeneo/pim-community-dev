export type AttributeGroupCode = string;

export type AttributeGroup = {
  code: AttributeGroupCode;
  sort_order: number;
  labels: {
    [locale: string]: string;
  };
};

export type AttributeGroupCollection = {
  [index: string]: AttributeGroup;
};
