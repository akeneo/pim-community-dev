import React from 'react';
import {renderWithProviders} from '../../tests';
import {ResetPassword} from './ResetPassword';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

test('it renders a reset password message, an email input and a reset password button', () => {
    renderWithProviders(<ResetPassword />);

    const resetPasswordMessage = screen.getByText('Please enter your email address to reset your password.');
    const emailInput = screen.getByLabelText('Email');
    const submitButton = screen.getByTestId('submit-button');

    expect(resetPasswordMessage).toBeInTheDocument();
    expect(emailInput).toBeInTheDocument();
    expect(submitButton).toBeInTheDocument();
});

test('it ensures the submit button is disabled if the email input is empty', () => {
    renderWithProviders(<ResetPassword />);

    const submitButton = screen.getByTestId('submit-button');

    expect(submitButton).toBeDisabled();
});

test('it ensures the submit button is enabled if the email input is not empty', () => {
    renderWithProviders(<ResetPassword />);

    const emailInput = screen.getByLabelText('Email');

    userEvent.type(emailInput, 'test@example.com');

    expect(screen.getByTestId('submit-button')).toBeEnabled();
});

test('it does not render the email input and the submit button if the form has been submitted', () => {
    renderWithProviders(<ResetPassword />);

    userEvent.type(screen.getByLabelText('Email'), 'test@example.com');
    userEvent.click(screen.getByTestId('submit-button'));

    expect(screen.queryByLabelText('Email')).toBeNull();
    expect(screen.queryByTestId('submit-button')).toBeNull();
    expect(
        screen.getByText('If the email address exists, an email has been send to reset your password.')
    ).toBeInTheDocument();
});
