import {
  createPermissionCollection,
  denormalizePermissionCollection,
  lowerLevel,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

const normalizedPermissions = [
  {
    user_group_identifier: 1,
    user_group_name: 'Manager',
    right_level: 'view',
  },
  {
    user_group_identifier: 2,
    user_group_name: 'IT',
    right_level: 'edit',
  },
  {
    user_group_identifier: 3,
    user_group_name: 'Translator',
    right_level: 'own',
  },
  {
    user_group_identifier: 4,
    user_group_name: 'Other',
    right_level: 'none',
  },
];

describe('akeneo > reference entity > domain > model > reference entity --- permission', () => {
  test('I can create a new permission collection', () => {
    expect(createPermissionCollection([]).normalize()).toEqual([]);

    expect(() => {
      createPermissionCollection(['cool']);
    }).toThrow('PermissionCollection expect only Permission objects as argument');

    expect(() => {
      denormalizePermissionCollection(['cool']);
    }).toThrow('Permission expect a number as userGroupIdentifier argument');

    expect(() => {
      denormalizePermissionCollection([
        {
          user_group_identifier: 12,
        },
      ]);
    }).toThrow('Permission expect a string as userGroupName argument');

    expect(() => {
      denormalizePermissionCollection([
        {
          user_group_identifier: 12,
          user_group_name: 'Manager',
        },
      ]);
    }).toThrow('Permission expect a string as rightLevel argument');
  });

  test('I can test if a collection is empty', () => {
    expect(createPermissionCollection([]).isEmpty()).toBe(true);
  });

  test('I can create a new permission collection from normalized', () => {
    expect(denormalizePermissionCollection(normalizedPermissions).normalize()).toEqual(normalizedPermissions);
    expect(denormalizePermissionCollection([]).normalize()).toEqual([]);
  });

  test('I can get a single permission', () => {
    expect(
      denormalizePermissionCollection(normalizedPermissions)
        .getPermission('Manager')
        .normalize()
    ).toEqual({
      user_group_identifier: 1,
      user_group_name: 'Manager',
      right_level: 'view',
    });

    expect(() => {
      denormalizePermissionCollection(normalizedPermissions).getPermission('Nice');
    }).toThrow('The permission for group "Nice" was not found in the permission collection');
  });

  test('I can get a lower level', () => {
    expect(lowerLevel('none')).toBe('none');
    expect(lowerLevel('view')).toBe('none');
    expect(lowerLevel('edit')).toBe('view');
    expect(lowerLevel('own')).toBe('edit');
    expect(() => lowerLevel('nice')).toThrow('The right level "nice" is not valid');
  });
});
