import $ from 'jquery';
import React, {FC} from 'react';
import {render, waitFor} from '@testing-library/react';
import AttributeGroupPermissionFormProvider from './AttributeGroupPermissionFormProvider';
import {PermissionFormProvider} from '@akeneo-pim-community/permission-form';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {GlobalWithFetchMock} from 'jest-fetch-mock';

// @ts-ignore
const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

jest.mock('require-context', () => ({
  __esModule: true,
  default: (module: string) => ({
    default: {module_that_should_be_imported: module},
  }),
}));

jest.mock('pim/security-context', () => ({
  isGranted: () => true,
}));

jest.mock('pim/feature-flags', () => ({
  isEnabled: () => true,
}));

jest.mock('routing', () => ({
  generate: route => route,
}));

const fetchByIdentifiers = jest.fn(() => Promise.resolve([]));

jest.mock('pim/fetcher-registry', () => ({
  getFetcher: () => ({
    fetchByIdentifiers,
  }),
}));

// @ts-ignore
const select2 = ($.fn.select2 = jest.fn());

type PermissionsFormState = any;

type PermissionsFormProps<T> = {
  provider: PermissionFormProvider<T>;
  onPermissionsChange: (state: T) => void;
  permissions: T | undefined;
  readOnly: boolean | undefined;
  onlyDisplayViewPermissions: boolean | undefined;
};

export const PermissionsForm: FC<PermissionsFormProps<PermissionsFormState>> = React.memo(
  ({provider, onPermissionsChange, permissions, readOnly, onlyDisplayViewPermissions}) => {
    return <div>{provider.renderForm(onPermissionsChange, permissions, readOnly, onlyDisplayViewPermissions)}</div>;
  }
);

type PermissionsSummaryProps<T> = {
  provider: PermissionFormProvider<T>;
  permissions: T | undefined;
};

export const PermissionsSummary: FC<PermissionsSummaryProps<PermissionsFormState>> = React.memo(
  ({provider, permissions}) => {
    return <div>{provider.renderSummary(permissions)}</div>;
  }
);

test('it renders the form without error', () => {
  render(
    <ThemeProvider theme={pimTheme}>
      <PermissionsForm
        provider={AttributeGroupPermissionFormProvider}
        onPermissionsChange={jest.fn()}
        permissions={{
          edit: {
            all: false,
            identifiers: [],
          },
          view: {
            all: false,
            identifiers: [],
          },
        }}
        readOnly={false}
        onlyDisplayViewPermissions={false}
      />
    </ThemeProvider>
  );
});

test('it renders the summary without error', async () => {
  render(
    <ThemeProvider theme={pimTheme}>
      <PermissionsSummary
        provider={AttributeGroupPermissionFormProvider}
        permissions={{
          edit: {
            all: false,
            identifiers: [],
          },
          view: {
            all: false,
            identifiers: [],
          },
        }}
      />
    </ThemeProvider>
  );

  await waitFor(() => expect(fetchByIdentifiers).toHaveBeenCalled());
});

test('it saves the permissions without error', async () => {
  await AttributeGroupPermissionFormProvider.save('my_user_group', {
    edit: {
      all: false,
      identifiers: [],
    },
    view: {
      all: false,
      identifiers: [],
    },
  });

  expect(fetch).toHaveBeenCalledWith('pimee_permissions_entities_set_attribute_groups', expect.anything());
});

test('it loads the permissions without error', async () => {
  fetchMock.mockResponseOnce(request => Promise.resolve({body: JSON.stringify({})}));

  await AttributeGroupPermissionFormProvider.loadPermissions('my_user_group');

  expect(fetch).toHaveBeenCalledWith('pimee_permissions_entities_get_user_group_attribute_groups', expect.anything());
});
