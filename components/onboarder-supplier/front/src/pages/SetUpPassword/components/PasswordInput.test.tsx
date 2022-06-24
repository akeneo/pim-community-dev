import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../../../tests';
import {PasswordInput} from './PasswordInput';

test('The password is hidden by default', async () => {
    renderWithProviders(<PasswordInput onChange={() => {}} value="my password" />);

    assertPasswordIsHidden();
});

test('The password can be displayed and hidden', async () => {
    renderWithProviders(<PasswordInput onChange={() => {}} value="my password" />);

    let showPasswordIcon = screen.getByTestId('show-password');
    await act(async () => {
        await userEvent.click(showPasswordIcon);
    });

    assertPasswordIsDisplayed();

    const hidePasswordIcon = screen.getByTestId('hide-password');
    await act(async () => {
        await userEvent.click(hidePasswordIcon);
    });

    assertPasswordIsHidden();
});

function assertPasswordIsHidden() {
    const input = screen.getByTestId('password-input');
    const hidePasswordIcon = screen.queryByTestId('hide-password');
    const showPasswordIcon = screen.queryByTestId('show-password');
    expect(input).toHaveAttribute('type', 'password');
    expect(showPasswordIcon).toBeInTheDocument();
    expect(hidePasswordIcon).not.toBeInTheDocument();
}

function assertPasswordIsDisplayed() {
    const input = screen.getByTestId('password-input');
    const hidePasswordIcon = screen.queryByTestId('hide-password');
    const showPasswordIcon = screen.queryByTestId('show-password');
    expect(input).toHaveAttribute('type', 'text');
    expect(showPasswordIcon).not.toBeInTheDocument();
    expect(hidePasswordIcon).toBeInTheDocument();
}
