import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {UserGroupsForm} from './UserGroupsForm';

let mockedGrantedACL = ['pim_user_group_index'];

jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => {
      return mockedGrantedACL.includes(acl);
    },
  }),
}));

jest.mock('../hooks/useUserGroups', () => ({
  useUserGroups: () => {
    return ['IT Support', 'Manager', 'Furniture manager', 'Clothes manager', 'Redactor', 'All'];
  },
}));

beforeEach(() => {
  mockedGrantedACL = ['pim_user_group_index'];
});

test('it renders the users form', () => {
  renderWithProviders(
    <UserGroupsForm
      userGroups={['IT Support']}
      validationErrors={[]}
      onUserGroupsChange={jest.fn()}
      label={'user_groups.label'}
      disabledHelperMessage={'user_groups.disabled_helper'}
    />
  );

  expect(screen.getByText('user_groups.label')).toBeInTheDocument();
  expect(screen.getByText('IT Support')).toBeInTheDocument();
});

test('it disables the user groups input if the user cannot list the user groups', () => {
  mockedGrantedACL = [];

  renderWithProviders(
    <UserGroupsForm
      userGroups={['IT Support']}
      validationErrors={[]}
      onUserGroupsChange={jest.fn()}
      label={'user_groups.label'}
      disabledHelperMessage={'user_groups.disabled_helper'}
    />
  );

  expect(screen.getByLabelText('user_groups.label')).toBeDisabled();
  expect(screen.getByText('user_groups.disabled_helper')).toBeInTheDocument();
});

test('it does not display the user groups "all"', () => {
  const onUserGroupsChange = jest.fn();

  renderWithProviders(
    <UserGroupsForm
      userGroups={['IT Support']}
      validationErrors={[]}
      onUserGroupsChange={onUserGroupsChange}
      label={'user_groups.label'}
      disabledHelperMessage={'user_groups.disabled_helper'}
    />
  );

  userEvent.click(screen.getByLabelText('user_groups.label'));
  expect(screen.queryByText('All')).not.toBeInTheDocument();
});

test('it can change the users', () => {
  const onUserGroupsChange = jest.fn();

  renderWithProviders(
    <UserGroupsForm
      userGroups={['IT Support']}
      validationErrors={[]}
      onUserGroupsChange={onUserGroupsChange}
      label={'user_groups.label'}
      disabledHelperMessage={'user_groups.disabled_helper'}
    />
  );

  userEvent.click(screen.getByLabelText('user_groups.label'));
  userEvent.click(screen.getByText('Redactor'));
  expect(onUserGroupsChange).toBeCalledWith(['IT Support', 'Redactor']);
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_type_error',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[]',
    },
  ];

  renderWithProviders(
    <UserGroupsForm
      userGroups={['IT Support']}
      validationErrors={validationErrors}
      onUserGroupsChange={jest.fn()}
      label={'user_groups.label'}
      disabledHelperMessage={'user_groups.disabled_helper'}
    />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});
