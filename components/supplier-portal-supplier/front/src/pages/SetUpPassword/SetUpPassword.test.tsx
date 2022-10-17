import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '../../tests';
import {SetUpPassword} from './SetUpPassword';
import {BadRequestError} from '../../api/BadRequestError';
import * as hook from './hooks/useContributorAccount';

beforeEach(() => {
    // @ts-ignore
    hook.useContributorAccount = jest.fn().mockReturnValue({
        loadingError: false,
        contributorAccount: {},
        submitPassword: () => {},
        passwordHasErrors: false,
    });
});

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
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(0);
});

test('it does not enable submit button if the password does not contain 8 characters', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1Aaaaaa'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1Aaaaaa'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(4);
});

test('it does not enable submit button if the password does not contain at least an uppercase letter', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1aaaaaaa'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1aaaaaaa'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(4);
});

test('it does not enable submit button if the password does not contain at least a lowercase letter', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: '1AAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1AAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(4);
});

test('it does not enable submit button if the password does not contain at least a number', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: 'aAAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: 'aAAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(4);
});

test('it does not enable submit button if the user has not consent to privacy policy', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');

    fireEvent.change(passwordInput, {target: {value: 'aAAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: 'aAAAAAAA'}});

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeDisabled();
});

test('it enables submit button if passwords are equal, match requirements and if the user consents to privacy policy', async () => {
    renderWithProviders(<SetUpPassword />);

    let passwordInput = screen.getByTestId('password-input');
    let confirmPasswordInput = screen.getByTestId('confirm-password-input');
    const consentCheckbox = screen.getByRole('checkbox');

    fireEvent.change(passwordInput, {target: {value: '1aAAAAAA'}});
    fireEvent.change(confirmPasswordInput, {target: {value: '1aAAAAAA'}});
    fireEvent.click(consentCheckbox);

    let submitButton = screen.getByTestId('submit-button');
    expect(submitButton).toBeEnabled();
    expect(screen.queryAllByTestId('password-rule-is-valid')).toHaveLength(5);
});

test('it renders the request new invitation page if the access token is expired', async () => {
    // @ts-ignore
    hook.useContributorAccount = jest.fn().mockReturnValue({
        loadingError: new BadRequestError([]),
        contributorAccount: {},
        submitPassword: () => {},
        passwordHasErrors: false,
    });
    renderWithProviders(<SetUpPassword />);

    const invitationExpiredMessage = screen.getByText(
        'Your invitation has expired. Please enter your email address to receive a new one.'
    );
    expect(invitationExpiredMessage).toBeInTheDocument();
});
