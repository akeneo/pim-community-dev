import {
  AttributeGroup,
  AttributeGroupCollection,
  AttributeGroupLabels,
} from '@akeneo-pim-community/settings-ui/src/models';

const anAttributeGroup = (
  code: string,
  id?: number,
  labels?: AttributeGroupLabels,
  order?: number,
  isDqiActivated?: boolean
): AttributeGroup => {
  let group: AttributeGroup = {
    code: code || 'a_code',
    labels: labels || {},
    sort_order: order !== undefined ? order : 1,
    attributes: [],
    attributes_sort_order: {},
    permissions: {
      view: [],
      edit: [],
    },
    meta: {
      id: id || 1234,
    },
  };
  if (isDqiActivated !== undefined) {
    group.isDqiActivated = isDqiActivated;
  }

  return group;
};

type AttributeGroupData = {
  code: string;
  id?: number;
  labels?: AttributeGroupLabels;
  order?: number;
};

const aListOfAttributeGroups = (data: AttributeGroupData[]) => {
  return data.map(row => anAttributeGroup(row.code, row.id, row.labels, row.order));
};

const aCollectionOfAttributeGroups = (data: AttributeGroupData[]): AttributeGroupCollection => {
  let collection: AttributeGroupCollection = {};

  data.forEach(row => {
    collection[row.code] = anAttributeGroup(row.code, row.id, row.labels, row.order);
  });

  return collection;
};

export {anAttributeGroup, aListOfAttributeGroups, aCollectionOfAttributeGroups};
