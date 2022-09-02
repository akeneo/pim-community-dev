import React from 'react';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../tests';
import {Login} from './Login';

const mockLogin = jest.fn();

jest.mock('./hooks/useAuthenticate', () => ({
    useAuthenticate: () => ({
        login: mockLogin,
    }),
}));

test('it renders the login page with an email, a password field and a forgot password link', () => {
    renderWithProviders(<Login />);

    expect(screen.getByText('Forgot your password?')).toBeInTheDocument();
    expect(screen.getByLabelText('Email')).toBeInTheDocument();
    expect(screen.getByLabelText('Password')).toBeInTheDocument();
});

test('it does not enable submit button if the email or password is empty', () => {
    renderWithProviders(<Login />);

    const submitButton = screen.getByTestId('submit-login');
    expect(submitButton).toBeInTheDocument();
    expect(submitButton).toBeDisabled();
});

test('it enables submit button when email and password are filled', () => {
    renderWithProviders(<Login />);

    const emailInput = screen.getByLabelText('Email');
    const passwordInput = screen.getByLabelText('Password');

    fireEvent.change(emailInput, {target: {value: 'burger@example.com'}});
    expect(screen.getByTestId('submit-login')).toBeDisabled();

    fireEvent.change(passwordInput, {target: {value: 'mypassword'}});
    expect(screen.getByTestId('submit-login')).toBeEnabled();

    fireEvent.change(emailInput, {target: {value: ''}});
    expect(screen.getByTestId('submit-login')).toBeDisabled();
});

test('it displays an error if credentials are wrong', async () => {
    mockLogin.mockImplementationOnce(() => false);

    renderWithProviders(<Login />);

    fireEvent.change(screen.getByLabelText('Email'), {target: {value: 'burger@example.com'}});
    fireEvent.change(screen.getByLabelText('Password'), {target: {value: 'mypassword'}});
    fireEvent.click(screen.getByTestId('submit-login'));

    await waitFor(() => {
        expect(screen.getByText('Your email or password seems to be wrong. Please, try again.')).toBeInTheDocument();
    });

    expect(mockLogin).toHaveBeenCalled();
});

test('it allows the user to log by submitting the form with the enter key', () => {
    renderWithProviders(<Login />);

    fireEvent.change(screen.getByLabelText('Email'), {target: {value: 'burger@example.com'}});
    fireEvent.change(screen.getByLabelText('Password'), {target: {value: 'mypassword'}});

    fireEvent.submit(screen.getByRole('form'));

    expect(mockLogin).toHaveBeenCalled();
});
