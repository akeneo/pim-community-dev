type AttributeGroup = {
  code: string;
  sort_order: number;
  attributes: string[];
  labels: {
    [locale: string]: string;
  };
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
};

type AttributeGroupCollection = {
  [group: string]: AttributeGroup;
};

const fromAttributeGroupsCollection = (collection: AttributeGroupCollection): AttributeGroup[] => {
  const groups = Object.values(collection).sort((groupA: AttributeGroup, groupB: AttributeGroup) => {
    return groupA.sort_order - groupB.sort_order;
  });

  return groups;
};

export {AttributeGroup, AttributeGroupCollection, fromAttributeGroupsCollection};
