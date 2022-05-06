import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../../../../test-utils';
import {ConsentCheckbox} from '@src/connect/components/AppWizard/steps/Authentication/ConsentCheckbox';
import userEvent from '@testing-library/user-event';

test('it renders correctly', async () => {
    renderWithProviders(<ConsentCheckbox isChecked={false} onChange={() => null} appUrl={null} />);

    await waitFor(() => screen.queryByRole('checkbox'));

    const label = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.label';
    const subtext = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.subtext';
    expect(screen.queryByText(label, {exact: false})).toBeInTheDocument();
    expect(screen.queryByText(subtext, {exact: false})).toBeInTheDocument();
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();
});

test('it renders correctly when checked', async () => {
    renderWithProviders(<ConsentCheckbox isChecked={true} onChange={() => null} appUrl={null} />);

    await waitFor(() => screen.queryByRole('checkbox'));

    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();
});

test('it calls onChange when checked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<ConsentCheckbox isChecked={false} onChange={onChange} appUrl={null} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: false})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(true, expect.anything());
});

test('it calls onChange when unchecked', async () => {
    const onChange = jest.fn();
    renderWithProviders(<ConsentCheckbox isChecked={true} onChange={onChange} appUrl={null} />);

    await waitFor(() => screen.queryByRole('checkbox'));
    expect(screen.queryByRole('checkbox', {checked: true})).toBeInTheDocument();

    act(() => userEvent.click(screen.getByRole('checkbox')));

    expect(onChange).toHaveBeenCalledWith(false, expect.anything());
});

test('it renders url provided', async () => {
    renderWithProviders(<ConsentCheckbox isChecked={true} onChange={() => null} appUrl={'testUrl'} />);

    await waitFor(() => screen.queryByRole('checkbox'));

    const linkLabel = 'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.contact_us';
    expect(screen.queryByText(linkLabel, {exact: false})).toBeInTheDocument();
    expect(screen.queryByText('testUrl', {exact: false})).toBeInTheDocument();
});
