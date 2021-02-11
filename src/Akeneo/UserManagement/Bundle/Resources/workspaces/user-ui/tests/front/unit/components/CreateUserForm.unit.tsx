import React from 'react';
import 'jest-fetch-mock';
import '@testing-library/jest-dom/extend-expect';
import {render, screen, act, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CreateUserForm} from '../../../../src/components';

const renderDataGridWithProviders = (
  userId: number,
  onCancel: () => void,
  onSuccess: (userId: string) => void,
  onError: () => void
) => {
  return render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <CreateUserForm userId={userId} onCancel={onCancel} onSuccess={onSuccess} onError={onError} />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

describe('CreateUserForm', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  test('it renders a CreateUserForm', () => {
    const onCancel = jest.fn();
    const onSuccess = jest.fn();
    const onError = jest.fn();

    renderDataGridWithProviders(1, onCancel, onSuccess, onError);
    expect(screen.getByText('pim_user_management.entity.user.properties.username')).toBeInTheDocument();
    expect(screen.getByText('pim_user_management.entity.user.properties.password')).toBeInTheDocument();
    expect(screen.getByText('pim_user_management.entity.user.properties.password_repeat')).toBeInTheDocument();
    expect(screen.getByText('pim_user_management.entity.user.properties.first_name')).toBeInTheDocument();
    expect(screen.getByText('pim_user_management.entity.user.properties.last_name')).toBeInTheDocument();
    expect(screen.getByText('pim_user_management.entity.user.properties.email')).toBeInTheDocument();
    expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();
    expect(screen.getByText('pim_common.confirm')).toBeInTheDocument();
  });

  test('it renders some errors when inputs are not filled', async () => {
    const onCancel = jest.fn();
    const onSuccess = jest.fn();
    const onError = jest.fn();

    renderDataGridWithProviders(1, onCancel, onSuccess, onError);

    expect(screen.queryByText('pim_user_management.form.error.required')).not.toBeInTheDocument();

    act(() => {
      fireEvent.submit(screen.getByTestId('form-create-user'));
    });
    expect(await screen.findAllByText('pim_user_management.form.error.required')).toHaveLength(6);
  });

  test('it successfully submit the form', async () => {
    fetchMock.mockResponses([JSON.stringify({meta: {id: 99}}), {status: 200}]);

    const onCancel = jest.fn();
    const onSuccess = jest.fn();
    const onError = jest.fn();

    renderDataGridWithProviders(1, onCancel, onSuccess, onError);

    const inputUsername = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.username pim_common.required_label'
    )) as HTMLInputElement;
    const inputPassword = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.password pim_common.required_label'
    )) as HTMLInputElement;
    const inputPasswordRepeat = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.password_repeat pim_common.required_label'
    )) as HTMLInputElement;
    const inputFirstName = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.first_name pim_common.required_label'
    )) as HTMLInputElement;
    const inputLastName = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.last_name pim_common.required_label'
    )) as HTMLInputElement;
    const inputEmail = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.email pim_common.required_label'
    )) as HTMLInputElement;

    await act(async () => {
      await userEvent.type(inputUsername, 'username');
      await userEvent.type(inputPassword, 'password');
      await userEvent.type(inputPasswordRepeat, 'password');
      await userEvent.type(inputFirstName, 'first_name');
      await userEvent.type(inputLastName, 'last_name');
      await userEvent.type(inputEmail, 'email');
      fireEvent.submit(screen.getByTestId('form-create-user'));
    });

    expect(onSuccess).toHaveBeenCalledTimes(1);
    expect(onSuccess).toHaveBeenCalledWith(99);
  });

  test('it displays errors returning by backend', async () => {
    fetchMock.mockResponses([
      JSON.stringify({
        values: [
          {path: 'password_repeat', message: 'passwords do not match'},
          {path: 'email', message: 'email is not valid'},
        ],
      }),
      {status: 400},
    ]);

    const onCancel = jest.fn();
    const onSuccess = jest.fn();
    const onError = jest.fn();

    renderDataGridWithProviders(1, onCancel, onSuccess, onError);

    const inputUsername = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.username pim_common.required_label'
    )) as HTMLInputElement;
    const inputPassword = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.password pim_common.required_label'
    )) as HTMLInputElement;
    const inputPasswordRepeat = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.password_repeat pim_common.required_label'
    )) as HTMLInputElement;
    const inputFirstName = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.first_name pim_common.required_label'
    )) as HTMLInputElement;
    const inputLastName = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.last_name pim_common.required_label'
    )) as HTMLInputElement;
    const inputEmail = (await screen.findByLabelText(
      'pim_user_management.entity.user.properties.email pim_common.required_label'
    )) as HTMLInputElement;

    await act(async () => {
      await userEvent.type(inputUsername, 'username');
      await userEvent.type(inputPassword, 'password');
      await userEvent.type(inputPasswordRepeat, 'password');
      await userEvent.type(inputFirstName, 'first_name');
      await userEvent.type(inputLastName, 'last_name');
      await userEvent.type(inputEmail, 'email');
      fireEvent.submit(screen.getByTestId('form-create-user'));
    });

    expect(onError).toHaveBeenCalledTimes(1);
    expect(await screen.findByText('passwords do not match')).toBeInTheDocument();
    expect(await screen.findByText('email is not valid')).toBeInTheDocument();
  });

  test('it firs the cancel action', async () => {
    const onCancel = jest.fn();
    const onSuccess = jest.fn();
    const onError = jest.fn();

    renderDataGridWithProviders(1, onCancel, onSuccess, onError);

    act(() => {
      userEvent.click(screen.getByText('pim_common.cancel'));
    });
    expect(onCancel).toHaveBeenCalledTimes(1);
  });
});
