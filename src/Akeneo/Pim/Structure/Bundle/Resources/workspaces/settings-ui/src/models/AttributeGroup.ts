import {Selection} from 'akeneo-design-system';

const DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP = 'other';
const LOCKED_ATTRIBUTE_GROUP_CODE = 'other';

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
  selection: Selection<AttributeGroup>,
  defaultTargetAttributeGroup: AttributeGroup | null
): [AttributeGroup[], AttributeGroup[]] => {
  const excludedAttributeGroups = attributeGroups.filter(
    attributeGroup => !selection.collection.includes(attributeGroup)
  );

  const [impactedAttributeGroups, targetAttributeGroups] =
    'in' === selection.mode
      ? [selection.collection, excludedAttributeGroups]
      : [excludedAttributeGroups, selection.collection];

  if (null === defaultTargetAttributeGroup) {
    return [impactedAttributeGroups, targetAttributeGroups];
  }

  return [
    impactedAttributeGroups,
    [
      defaultTargetAttributeGroup,
      ...targetAttributeGroups.filter(({code}) => defaultTargetAttributeGroup.code !== code),
    ],
  ];
};

export {
  AttributeGroup,
  AttributeGroupCollection,
  AttributeGroupLabels,
  DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP,
  LOCKED_ATTRIBUTE_GROUP_CODE,
  getImpactedAndTargetAttributeGroups,
  toSortedAttributeGroupsArray,
};
