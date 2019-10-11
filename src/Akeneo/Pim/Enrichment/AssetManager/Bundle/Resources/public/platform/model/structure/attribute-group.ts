export type AttributeGroupCode = string;

export type AttributeGroup = {
  code: AttributeGroupCode;
  sort_order: number;
};

export type AttributeGroupCollection = {
  [index: string]: AttributeGroup;
};
