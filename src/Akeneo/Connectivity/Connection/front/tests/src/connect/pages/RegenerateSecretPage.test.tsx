import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {RegenerateSecretPage} from '@src/connect/pages/RegenerateSecretPage';
import {MemoryRouter, Route, Router} from 'react-router-dom';
import {act, screen} from '@testing-library/react';
import {createMemoryHistory} from 'history';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

const connectedAppResponse = {
    loading: false,
    error: null,
    payload: {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'Custom App A',
        scopes: ['view_catalog_structure', 'read_products'],
        connection_code: 'custom_app',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        is_custom_app: true,
        is_pending: false,
        has_outdated_scopes: false,
    },
};
jest.mock('@src/connect/hooks/use-connected-app', () => ({
    useConnectedApp: () => connectedAppResponse,
}));

test('The regenerate secret modal return null if no connectedApp found or still loading', () => {
    const {container} = renderWithProviders(<RegenerateSecretPage />);
    expect(container).toBeEmptyDOMElement();
});

test('The regenerate secret modal renders the regenerate step by default', async () => {
    renderWithProviders(
        <MemoryRouter initialEntries={['connect/connected-apps/custom_app/regenerate-secret']}>
            <Route path='connect/connected-apps/:connectionCode/regenerate-secret'>
                <RegenerateSecretPage />
            </Route>
        </MemoryRouter>
    );

    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.title'
        )
    ).toBeInTheDocument();
    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
        )
    ).toBeInTheDocument();
});

test('The regenerate secret modal redirect to the connected app page when closed', async () => {
    const history = createMemoryHistory({initialEntries: ['connect/connected-apps/custom_app']});
    renderWithProviders(
        <Router history={history}>
            <MemoryRouter initialEntries={['connect/connected-apps/custom_app/regenerate-secret']}>
                <Route path='connect/connected-apps/:connectionCode/regenerate-secret'>
                    <RegenerateSecretPage />
                </Route>
            </MemoryRouter>
        </Router>
    );

    expect(screen.queryByTitle('pim_common.close')).toBeInTheDocument();

    await act(async () => userEvent.click(await screen.findByTitle('pim_common.close')));

    expect(history.location.pathname).toBe('connect/connected-apps/custom_app');
});

test('The regenerate secret modal redirect to the connected app page when the cancel button is clicked', async () => {
    const history = createMemoryHistory({initialEntries: ['connect/connected-apps/custom_app']});
    renderWithProviders(
        <Router history={history}>
            <MemoryRouter initialEntries={['connect/connected-apps/custom_app/regenerate-secret']}>
                <Route path='connect/connected-apps/:connectionCode/regenerate-secret'>
                    <RegenerateSecretPage />
                </Route>
            </MemoryRouter>
        </Router>
    );

    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.cancel_button'
        )
    ).toBeInTheDocument();

    await act(async () =>
        userEvent.click(
            await screen.findByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.cancel_button'
            )
        )
    );

    expect(history.location.pathname).toBe('connect/connected-apps/custom_app');
});

test('The regenerate secret modal regenerate a new secret when the regenerate button is clicked then redirect to the connect app page when the done button is clicked', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_regenerate_secret?customAppId=0dfce574-2238-4b13-b8cc-8d257ce7645b':
            {
                json: 'NjVmMGMzNDdkM2RkNjMyOTFmNDI1NTdhNjA2NGNkZjQyODc3ZWYxZGY3NjJmYTEzYmQyMGE5ZDZkMGU4YmVhYw',
            },
    });

    const history = createMemoryHistory({initialEntries: ['connect/connected-apps/custom_app']});
    renderWithProviders(
        <Router history={history}>
            <MemoryRouter initialEntries={['connect/connected-apps/custom_app/regenerate-secret']}>
                <Route path='connect/connected-apps/:connectionCode/regenerate-secret'>
                    <RegenerateSecretPage />
                </Route>
            </MemoryRouter>
        </Router>
    );

    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
        )
    ).toBeInTheDocument();

    await act(async () =>
        userEvent.click(
            await screen.findByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
            )
        )
    );

    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.subtitle'
        )
    ).toBeInTheDocument();

    expect(await screen.findByText(connectedAppResponse.payload.id)).toBeInTheDocument();
    expect(
        await screen.findByText(
            'NjVmMGMzNDdkM2RkNjMyOTFmNDI1NTdhNjA2NGNkZjQyODc3ZWYxZGY3NjJmYTEzYmQyMGE5ZDZkMGU4YmVhYw'
        )
    ).toBeInTheDocument();

    expect(
        await screen.findByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.done_button'
        )
    ).toBeInTheDocument();

    await act(async () =>
        userEvent.click(
            await screen.findByText(
                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.new_credentials.done_button'
            )
        )
    );

    expect(history.location.pathname).toBe('connect/connected-apps/custom_app');
});
