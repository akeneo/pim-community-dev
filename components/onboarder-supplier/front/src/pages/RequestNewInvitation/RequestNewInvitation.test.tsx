import React from 'react';
import {screen} from '@testing-library/react';
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
