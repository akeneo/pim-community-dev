export type AttributeGroupCode = string;

export type AttributeGroup = {
  code: AttributeGroupCode;
  sort_order: string;
};

export type AttributeGroupCollection = {
  [index: string]: AttributeGroup;
};
