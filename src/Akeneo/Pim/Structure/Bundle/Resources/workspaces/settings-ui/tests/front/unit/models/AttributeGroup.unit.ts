import {toSortedAttributeGroupsArray} from '@akeneo-pim-community/settings-ui/src/models';
import {aCollectionOfAttributeGroups} from '../../utils/provideAttributeGroupHelper';

describe('toSortedAttributeGroupsArray', () => {
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
});
