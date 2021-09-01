import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, MockFetchResponses, renderWithProviders, historyMock} from '../../../../test-utils';
import {AppWizard} from '@src/connect/components/AppWizard/AppWizard';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The wizard renders without error', async () => {
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

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));
    expect(screen.getByAltText('MyApp')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.title')).toBeInTheDocument();
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

    renderWithProviders(<AppWizard clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByTitle('akeneo_connectivity.connection.connect.apps.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/marketplace');
});
