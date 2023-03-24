import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeGroupsCreateButton} from '@akeneo-pim-community/settings-ui';

const redirectToRouteMock = jest.fn();
const isGrantedMock = jest.fn();

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useRouter: () => ({
    redirectToRoute: redirectToRouteMock,
  }),
  useSecurity: () => ({
    isGranted: isGrantedMock,
  }),
}));

beforeEach(() => {
  isGrantedMock.mockImplementation(() => true);
});

test('it redirects to attribute group create page', () => {
  renderWithProviders(<AttributeGroupsCreateButton attributeGroupCount={1} />);

  userEvent.click(screen.getByText('pim_common.create'));

  expect(redirectToRouteMock).toBeCalledWith('pim_enrich_attributegroup_create');
});

test('it renders nothing if user does not have the acl enabled', () => {
  isGrantedMock.mockImplementation(() => false);

  renderWithProviders(<AttributeGroupsCreateButton attributeGroupCount={1} />);

  expect(screen.queryByText('pim_common.create')).not.toBeInTheDocument();
});

test('it disables creation when attribute groups limit is reached', () => {
  renderWithProviders(<AttributeGroupsCreateButton attributeGroupCount={1000} />);

  expect(screen.getByText('pim_common.create')).toBeDisabled();
});
