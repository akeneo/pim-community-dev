type AttributeGroupLabels = {
  [locale: string]: string;
};

export type AttributeGroup = {
  code: string;
  sort_order: number;
  attributes: string[];
  labels: AttributeGroupLabels;
  permissions: {
    view: string[];
    edit: string[];
  };
  attributes_sort_order: {
    [attribute: string]: number;
  };
  meta: {
    id: number;
  };
  isDqiActivated?: boolean;
};

export type AttributeGroupCollection = {
  [group: string]: AttributeGroup;
};
