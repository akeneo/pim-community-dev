type AttributeGroupLabels = {
  [locale: string]: string;
};

type AttributeGroup = {
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

type AttributeGroupCollection = {
  [group: string]: AttributeGroup;
};

const toSortedAttributeGroupsArray = (collection: AttributeGroupCollection): AttributeGroup[] => {
  return Object.values(collection).sort((groupA: AttributeGroup, groupB: AttributeGroup) => {
    return groupA.sort_order - groupB.sort_order;
  });
};

export {AttributeGroup, AttributeGroupCollection, AttributeGroupLabels, toSortedAttributeGroupsArray};
