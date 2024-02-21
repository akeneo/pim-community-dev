import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditPermissionsForm} from './EditPermissionsForm';
import {EnrichCategory} from '../models';
import {QueryClient, QueryClientProvider} from 'react-query';

const permissions = {
  own: [{id: 1, label: 'IT Manager'}],
  edit: [{id: 1, label: 'IT Manager'}],
  view: [{id: 1, label: 'IT Manager'}],
};

const category: EnrichCategory = {
  id: 1,
  isRoot: true,
  root: null,
  template_uuid: null,
  properties: {
    code: 'test',
    labels: {},
  },
  attributes: {},
  permissions: permissions,
};

const queryClient = new QueryClient();
test('It renders the permissions page', () => {
  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <EditPermissionsForm
        category={category}
        applyPermissionsOnChildren={false}
        onChangePermissions={() => {}}
        onChangeApplyPermissionsOnChildren={() => {}}
      />
    </QueryClientProvider>
  );

  expect(screen.getByText(/category.permissions.view.label/)).toBeInTheDocument();
  expect(screen.getByText(/category.permissions.edit.label/)).toBeInTheDocument();
  expect(screen.getByText(/category.permissions.own.label/)).toBeInTheDocument();
});

test('It renders empty permissions values', () => {
  const emptyPermissions = {
    own: [],
    edit: [],
    view: [],
  };
  category.permissions = emptyPermissions;
  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <EditPermissionsForm
        category={category}
        applyPermissionsOnChildren={false}
        onChangePermissions={() => {}}
        onChangeApplyPermissionsOnChildren={() => {}}
      />
    </QueryClientProvider>
  );

  expect(screen.getByText(/category.permissions.view.label/)).toBeInTheDocument();
  expect(screen.getByText(/category.permissions.edit.label/)).toBeInTheDocument();
  expect(screen.getByText(/category.permissions.own.label/)).toBeInTheDocument();
});
