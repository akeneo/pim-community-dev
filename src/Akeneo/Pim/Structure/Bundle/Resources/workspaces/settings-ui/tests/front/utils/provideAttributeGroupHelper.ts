import {AttributeGroup, AttributeGroupCollection, AttributeGroupLabels} from '@akeneo-pim-community/settings-ui';

const anAttributeGroup = (
  code: string,
  id: number,
  labels: AttributeGroupLabels,
  order: number,
  is_dqi_activated = false,
  attribute_count = 10
): AttributeGroup => {
  const group: AttributeGroup = {
    code: code || 'a_code',
    labels: labels || {},
    sort_order: order !== undefined ? order : 1,
    is_dqi_activated: is_dqi_activated === true,
    attribute_count: attribute_count,
  };

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
