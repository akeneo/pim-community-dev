import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../../tests';
import {RequestNewInvitation} from './RequestNewInvitation';

test('it renders an invitation has expired message and an email input', async () => {
    renderWithProviders(<RequestNewInvitation />);

    const emailInput = screen.getByTestId('email-input');
    const invitationExpiredMessage = screen.getByText(
        'Your invitation has expired. Please enter your email address to receive a new one.'
    );

    expect(emailInput).toBeInTheDocument();
    expect(invitationExpiredMessage).toBeInTheDocument();
});

test('it does not render a message saying that an email will be send if the form has not been submitted', () => {
    renderWithProviders(<RequestNewInvitation />);

    expect(
        screen.queryByText('An email will be send in a few moments. Please check your emails to access the service.')
    ).toBeNull();
});

test('it renders a message saying that an email will be send when the form has been submitted', () => {
    renderWithProviders(<RequestNewInvitation />);

    const emailInput = screen.getByTestId('email-input');
    const submitButton = screen.getByTestId('submit-button');

    userEvent.type(emailInput, 'test@example.com');
    userEvent.click(submitButton);

    expect(
        screen.getByText('An email will be send in a few moments. Please check your emails to access the service.')
    ).toBeInTheDocument();
});
