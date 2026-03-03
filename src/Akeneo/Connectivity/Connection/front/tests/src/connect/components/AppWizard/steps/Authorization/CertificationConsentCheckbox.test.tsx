import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../../../../test-utils';
import {CertificationConsentCheckbox} from '@src/connect/components/AppWizard/steps/Authorization/CertificationConsentCheckbox';
import userEvent from '@testing-library/user-event';

test('it renders correctly', async () => {
    renderWithProviders(<CertificationConsentCheckbox isChecked={false} onChange={() => null} />);

    await waitFor(() => screen.queryByRole('checkbox'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.certification_consent.label')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.certification_consent.subtext')
    ).toBeInTheDocument();
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();
});

test('it renders correctly when checked', async () => {
    renderWithProviders(<CertificationConsentCheckbox isChecked={true} onChange={() => null} />);

    await waitFor(() => screen.queryByRole('checkbox'));

    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();
});

test('it calls onChange when checked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<CertificationConsentCheckbox isChecked={false} onChange={onChange} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(true, expect.anything());
});

test('it calls onChange when unchecked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<CertificationConsentCheckbox isChecked={true} onChange={onChange} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(false, expect.anything());
});
