import {Selection} from 'akeneo-design-system';

type AttributeGroupLabels = {
  [locale: string]: string;
};

type AttributeGroup = {
  code: string;
  sort_order: number;
  labels: AttributeGroupLabels;
  is_dqi_activated: boolean;
  attribute_count: number;
};

type AttributeGroupCollection = {
  [group: string]: AttributeGroup;
};

const toSortedAttributeGroupsArray = (collection: AttributeGroupCollection): AttributeGroup[] => {
  return Object.values(collection).sort((groupA: AttributeGroup, groupB: AttributeGroup) => {
    return groupA.sort_order - groupB.sort_order;
  });
};

const getImpactedAndTargetAttributeGroups = (
  attributeGroups: AttributeGroup[],
  selection: Selection<AttributeGroup>
): [number, AttributeGroup[]] => {
  const excludedAttributeGroups = attributeGroups.filter(
    attributeGroup => !selection.collection.includes(attributeGroup)
  );

  const [impactedAttributeGroups, targetAttributeGroups] =
    'in' === selection.mode
      ? [selection.collection, excludedAttributeGroups]
      : [excludedAttributeGroups, selection.collection];

  return [
    impactedAttributeGroups.reduce((totalCount, {attribute_count}) => totalCount + attribute_count, 0),
    targetAttributeGroups,
  ];
};

export {
  AttributeGroup,
  AttributeGroupCollection,
  AttributeGroupLabels,
  getImpactedAndTargetAttributeGroups,
  toSortedAttributeGroupsArray,
};
