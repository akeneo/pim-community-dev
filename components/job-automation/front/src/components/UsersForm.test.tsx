import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {UsersForm} from './UsersForm';

let mockedGrantedACL = ['pim_user_user_index'];

jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => {
      return mockedGrantedACL.includes(acl);
    },
  }),
}));

jest.mock('../hooks/useUsers', () => ({
  useUsers: () => {
    return ['admin', 'julia', 'julien', 'mary', 'pamela', 'peter', 'sandra'];
  },
}));

beforeEach(() => {
  mockedGrantedACL = ['pim_user_user_index'];
});

test('it renders the users form', () => {
  renderWithProviders(<UsersForm users={['admin']} validationErrors={[]} onUsersChange={jest.fn()} />);

  expect(screen.getByText('akeneo.job_automation.notification.users.label')).toBeInTheDocument();
  expect(screen.getByText('admin')).toBeInTheDocument();
});

test('it disables the users input if the user cannot list the users', () => {
  mockedGrantedACL = [];

  renderWithProviders(<UsersForm users={['admin']} validationErrors={[]} onUsersChange={jest.fn()} />);

  expect(screen.getByLabelText('akeneo.job_automation.notification.users.label')).toBeDisabled();
  expect(screen.getByText('akeneo.job_automation.notification.users.disabled_helper')).toBeInTheDocument();
});

test('it can change the users', () => {
  const onUsersChange = jest.fn();

  renderWithProviders(<UsersForm users={['admin']} validationErrors={[]} onUsersChange={onUsersChange} />);

  userEvent.click(screen.getByLabelText('akeneo.job_automation.notification.users.label'));
  userEvent.click(screen.getByText('julia'));
  expect(onUsersChange).toBeCalledWith(['admin', 'julia']);
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

  renderWithProviders(<UsersForm users={['admin']} validationErrors={validationErrors} onUsersChange={jest.fn()} />);

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});
