import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '../../tests';
import {SetUpPassword} from './SetUpPassword';

jest.mock('./hooks/useContributorAccount', () => ({
    useContributorAccount: () => ({
        loadingError: false,
        contributorAccount: {},
        submitPassword: () => {},
        passwordHasErrors: false,
    }),
}));

test('it renders the password input with its confirmation input', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    expect(passwordInput).toBeInTheDocument();
    expect(confirmPasswordInput).toBeInTheDocument();
});

test('it renders password type inputs', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    expect(passwordInput).toHaveAttribute('type', 'password');
    expect(confirmPasswordInput).toHaveAttribute('type', 'password');
});

test('it does not enable submit button if the password does not contain 8 characters', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1Aaaaaa'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1Aaaaaa'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
});

test('it does not enable submit button if the password does not contain at least an uppercase letter', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1aaaaaaa'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1aaaaaaa'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
});

test('it does not enable submit button if the password does not contain at least a lowercase letter', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1AAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1AAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
});

test('it does not enable submit button if the password does not contain at least a number', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: 'aAAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: 'aAAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
});

test('it enables submit button if passwords are equal and match requirements', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1aAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1aAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeEnabled();
});
