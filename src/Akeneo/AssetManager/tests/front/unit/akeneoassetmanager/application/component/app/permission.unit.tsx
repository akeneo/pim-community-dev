import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {mount as baseMount} from 'enzyme';
import {PermissionCollectionEditor} from 'akeneoassetmanager/tools/component/permission';
import {denormalizePermissionCollection, RightLevel} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const mount = (element: any) =>
  baseMount(element, {wrappingComponent: ({children}) => <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>});

const assertRightLevel = (element: any, groupName: string, rightLevel: string) => {
  ['none', 'view', 'edit', 'own'].reduce((previous: string[], current: string) => {
    expect(
      element
        .find(
          `.AknPermission-row[data-user-group-code="${groupName}"] .AknPermission-level[data-right-level="${current}"]`
        )
        .exists('.AknPermission-pill--active')
    ).toBe(!previous.includes(rightLevel));

    return [...previous, current];
  }, []);
};

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
  },
]);

describe('>>>COMPONENT --- permission', () => {
  test('Display a simple permission table', () => {
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {}}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    assertRightLevel(permissionsEditor, 'Manager', 'view');
    assertRightLevel(permissionsEditor, 'Translator', 'edit');
    assertRightLevel(permissionsEditor, 'IT', 'own');
    assertRightLevel(permissionsEditor, 'Other', 'none');
  });

  test('Mass update rights', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('edit');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor.find(`.AknPermission-header button[data-right-level="edit"]`).simulate('click');
  });

  test('Mass update rights with keyboard', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(true).toEqual(false);
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor.find(`.AknPermission-header button[data-right-level="edit"]`).simulate('keyDown', {
      keyCode: 40,
      which: 40,
      key: 'arrow down',
    });
  });

  test('Mass update rights at none', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('none');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('none');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('none');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor.find(`.AknPermission-header button[data-right-level="none"]`).simulate('click');
  });

  test('Mass update rights on read only', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={true}
          onChange={newPermissions => {
            expect(true).toEqual(false); // This is intended. The test is to check that the onChange method is not called
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor.find(`.AknPermission-header button[data-right-level="edit"]`).simulate('click');
  });

  test('Update one right', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('own');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('own');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="Translator"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('click');
  });

  test('Update one right with keyboard', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('own');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('own');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="Translator"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('keyDown', {
        keyCode: 32,
        which: 32,
        key: ' ',
      });
  });

  test('Update one right on read only mode', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={true}
          onChange={newPermissions => {
            expect(true).toEqual(false); // This is intended. The test is to check that the onChange method is not called
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="Translator"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('click');
  });

  test('Toggle one right', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="IT"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('click');
  });

  test('Toggle one right with keyboard', () => {
    expect.assertions(4);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(newPermissions.getPermission('Manager').getRightLevel()).toEqual('view');
            expect(newPermissions.getPermission('Translator').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('IT').getRightLevel()).toEqual('edit');
            expect(newPermissions.getPermission('Other').getRightLevel()).toEqual('none');
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="IT"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('keyDown', {
        keyCode: 32,
        which: 32,
        key: ' ',
      });
  });

  test('Toggle one right with keyboard on wrong key', () => {
    expect.assertions(0);
    const permissionsEditor = mount(
      <DependenciesProvider>
        <PermissionCollectionEditor
          value={permissions}
          readOnly={false}
          onChange={newPermissions => {
            expect(true).toEqual(false);
          }}
          prioritizedRightLevels={[RightLevel.None, RightLevel.View, RightLevel.Edit, RightLevel.Own]}
        />
      </DependenciesProvider>
    );

    permissionsEditor
      .find(
        `.AknPermission-row[data-user-group-code="IT"] .AknPermission-level[data-right-level="own"] .AknPermission-pill`
      )
      .simulate('keyDown', {
        keyCode: 40,
        which: 40,
        key: 'arrow down',
      });
  });
});
