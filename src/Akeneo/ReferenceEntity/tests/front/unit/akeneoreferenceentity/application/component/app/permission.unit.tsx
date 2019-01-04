import PermissionCollectionEditor from 'akeneoreferenceentity/tools/component/permission';
import {denormalizePermissionCollection, RightLevel} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

import * as React from 'react';
import {mount} from 'enzyme';

const assertRightLevel = (element: any, groupName: string, rightLevel: string) => {
  ['none', 'view', 'edit', 'own'].reduce((previous: string[], current: string) => {
    expect(
      element
        .find(`.AknPermission-row[data-user-group-code="${groupName}"] .AknPermission-level[data-right-level="${current}"]`).exists('.AknPermission-pill--active')
    ).toBe(!previous.includes(rightLevel));

    return [...previous, current];
  }, []);
}

const permissions = denormalizePermissionCollection([
  {
    user_group_identifier: 1,
    user_group_name: 'Manager',
    right_level: 'view',
  },
  {
    user_group_identifier: 2,
    user_group_name: 'Translator',
    right_level: 'edit',
  },
  {
    user_group_identifier: 3,
    user_group_name: 'IT',
    right_level: 'own',
  },
  {
    user_group_identifier: 4,
    user_group_name: 'Other',
    right_level: 'none',
  }
]);

describe('>>>COMPONENT --- permission', () => {
  test('Display a simple permission table', () => {
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={false}
        onChange={(newPermissions) => {
          console.log(newPermissions)
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    assertRightLevel(permissionsEditor, 'Manager', 'view');
    assertRightLevel(permissionsEditor, 'Translator', 'edit');
    assertRightLevel(permissionsEditor, 'IT', 'own');
    assertRightLevel(permissionsEditor, 'Other', 'none');
  });

  test('Mass update rights', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={false}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('edit')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row--massAction .AknPermission-level[data-right-level="edit"] .AknPermission-pill`).simulate('click')
  });

  test('Mass update rights at none', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={false}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('none')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('none')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('none')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row--massAction .AknPermission-level[data-right-level="none"] .AknPermission-pill`).simulate('click')
  });

  test('Mass update rights on read only', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={true}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('own')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row--massAction .AknPermission-level[data-right-level="edit"] .AknPermission-pill`).simulate('click')
  });

  test('Update one right', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={false}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('own')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('own')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row[data-user-group-code="Translator"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`).simulate('click')
  });

  test('Update one right', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={true}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('own')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('own')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row[data-user-group-code="Translator"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`).simulate('click')
  });

  test('Toggle one right', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <PermissionCollectionEditor
        value={permissions}
        readOnly={false}
        onChange={(newPermissions) => {
          expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view')
          expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('edit')
          expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none')
        }}
        prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
      />
    );

    permissionsEditor.find(`.AknPermission-row[data-user-group-code="IT"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`).simulate('click')
  });
});
