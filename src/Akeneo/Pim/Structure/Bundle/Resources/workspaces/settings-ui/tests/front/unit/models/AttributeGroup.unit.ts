import {
  AttributeGroup,
  DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP,
  getImpactedAndTargetAttributeGroups,
  toSortedAttributeGroupsArray,
} from '@akeneo-pim-community/settings-ui/src/models';
import {Selection} from 'akeneo-design-system';
import {aCollectionOfAttributeGroups} from '../../utils/provideAttributeGroupHelper';

test('it builds an array of attribute groups, sorted by the sort_order property', () => {
  const collection = aCollectionOfAttributeGroups([
    {code: 'groupA', order: 2},
    {code: 'groupB', order: 3},
    {code: 'groupC', order: 1},
  ]);

  const result = toSortedAttributeGroupsArray(collection);

  expect(result.length).toBe(3);
  expect(result[0].code).toBe('groupC');
  expect(result[1].code).toBe('groupA');
  expect(result[2].code).toBe('groupB');
});

test('it can get the total number of impacted attribute groups children & target attribute groups and always put the default attribute group first', () => {
  const defaultAttributeGroup: AttributeGroup = {
    code: DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP,
    labels: {},
    sort_order: 2,
    is_dqi_activated: false,
    attribute_count: 7,
  };

  const attributeGroups: AttributeGroup[] = [
    {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
    {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
    defaultAttributeGroup,
    {code: 'attribute_group_3', labels: {}, sort_order: 3, is_dqi_activated: false, attribute_count: 2},
  ];

  const selection: Selection<AttributeGroup> = {
    collection: [attributeGroups[0], attributeGroups[2]],
    mode: 'in',
  };

  expect(getImpactedAndTargetAttributeGroups(attributeGroups, selection, defaultAttributeGroup)).toEqual([
    [attributeGroups[0], attributeGroups[2]],
    [attributeGroups[2], attributeGroups[1], attributeGroups[3]],
  ]);

  expect(
    getImpactedAndTargetAttributeGroups(attributeGroups, {...selection, mode: 'not_in'}, defaultAttributeGroup)
  ).toEqual([
    [attributeGroups[1], attributeGroups[3]],
    [attributeGroups[2], attributeGroups[0]],
  ]);
});
