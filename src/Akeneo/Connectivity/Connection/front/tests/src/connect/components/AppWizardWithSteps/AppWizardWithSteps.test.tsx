import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitForElement} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {historyMock, mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {AppWizardWithSteps} from '@src/connect/components/AppWizardWithSteps/AppWizardWithSteps';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('@src/connect/components/AppWizardWithSteps/Authorizations', () => ({
    Authorizations: () => <div>authorizations-component</div>,
}));
jest.mock('@src/connect/components/AppWizardWithSteps/Permissions', () => ({
    Permissions: () => <div>permissions-component</div>,
}));
test('The step wizard renders without error', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));
    expect(screen.queryByAltText('MyApp')).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();
});

test('The wizard redirect to the marketplace when closed', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByTitle('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/marketplace');
});

test('The wizard renders steps', async () => {
    // TODO Add "Well done" step when done
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous'));
    });

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();
});
