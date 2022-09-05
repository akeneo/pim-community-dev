import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {act, screen, waitFor} from '@testing-library/react';
import {Marketplace} from '@src/connect/components/Marketplace';
import {MarketplaceHelper} from '@src/connect/components/MarketplaceHelper';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {useSecurity} from '@src/shared/security';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('@src/shared/feature-flags/use-feature-flags');
jest.mock('@src/shared/security/use-security');
jest.mock('@src/connect/components/MarketplaceHelper', () => ({
    MarketplaceHelper: jest.fn(() => <div>MarketplaceHelper</div>),
}));

test('The marketplace renders with apps', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
                app_developer_mode: false,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
                akeneo_connectivity_connection_manage_test_apps: false,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
    });

    const apps = {
        total: 1,
        apps: [
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                name: 'App A',
                logo: 'http://www.example.com/path/to/logo/a',
                author: 'author A',
                partner: 'Akeneo Partner',
                description: 'Our Akeneo App',
                url: 'https://marketplace.akeneo.com/apps/app_a',
                categories: ['E-commerce'],
                certified: false,
                activate_url: 'https://example.com/activate',
                callback_url: 'https://example.com/oauth2',
                connected: false,
                isPending: false,
            },
        ],
    };
    const testApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} testApps={testApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=1')
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.empty')).not.toBeInTheDocument();
    expect(screen.getByText('App A')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=0')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.empty')).toBeInTheDocument();
});

test('The marketplace renders with extensions', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
                app_developer_mode: false,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
                akeneo_connectivity_connection_manage_test_apps: false,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
    });

    const apps = {
        total: 0,
        apps: [],
    };
    const testApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 1,
        extensions: [
            {
                id: '6fec7055-36ad-4301-9889-46c46ddd446a',
                name: 'Extension A',
                logo: 'https://marketplace.test/logo/extension_1.png',
                author: 'Partner A',
                partner: 'Akeneo Partner',
                description: 'Our Akeneo Connector',
                url: 'https://marketplace.test/extension/extension_1',
                categories: ['E-commerce'],
                certified: false,
            },
        ],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} testApps={testApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=0')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.empty')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=1')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.extensions.empty')
    ).not.toBeInTheDocument();
    expect(screen.getByText('Extension A')).toBeInTheDocument();
});

test('The marketplace renders with test apps', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
                app_developer_mode: true,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
                akeneo_connectivity_connection_manage_test_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
    });

    const apps = {
        total: 0,
        apps: [],
    };
    const testApps = {
        total: 1,
        apps: [
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                name: 'Test App A',
                logo: null,
                author: null,
                url: null,
                activate_url: 'test_app_a_activate_url',
                callback_url: 'test_app_a_callback_url',
                connected: false,
            },
        ],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} testApps={testApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')).toBeInTheDocument();
    expect(screen.getByText('Test App A')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=0')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.empty')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=0')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.empty')).toBeInTheDocument();
});

test('The search input filters apps and extensions', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
                app_developer_mode: false,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
                akeneo_connectivity_connection_manage_test_apps: false,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
    });

    const apps = {
        total: 2,
        apps: [
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                name: 'First App',
                logo: 'http://www.example.com/path/to/logo/a',
                author: 'author A',
                partner: 'Akeneo Partner',
                description: 'Our Akeneo App',
                url: 'https://marketplace.akeneo.com/apps/app_a',
                categories: ['E-commerce'],
                certified: false,
                activate_url: 'https://example.com/activate',
                callback_url: 'https://example.com/oauth2',
                connected: false,
                isPending: false,
            },
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645c',
                name: 'Second App',
                logo: 'http://www.example.com/path/to/logo/b',
                author: 'author B',
                partner: 'Akeneo Partner',
                description: 'Our second Akeneo App',
                url: 'https://marketplace.akeneo.com/apps/app_b',
                categories: ['E-commerce'],
                certified: false,
                activate_url: 'https://example.com/activate',
                callback_url: 'https://example.com/oauth2',
                connected: false,
                isPending: false,
            },
        ],
    };
    const testApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 2,
        extensions: [
            {
                id: '6fec7055-36ad-4301-9889-46c46ddd446a',
                name: 'First Extension',
                logo: 'https://marketplace.test/logo/extension_1.png',
                author: 'Partner A',
                partner: 'Akeneo Partner',
                description: 'Our Akeneo Connector',
                url: 'https://marketplace.test/extension/extension_1',
                categories: ['E-commerce'],
                certified: false,
            },
            {
                id: '6fec7055-36ad-4301-9889-46c46ddd446b',
                name: 'Second Extension',
                logo: 'https://marketplace.test/logo/extension_2.png',
                author: 'Partner B',
                partner: 'Akeneo Partner',
                description: 'Our second Akeneo Connector',
                url: 'https://marketplace.test/extension/extension_2',
                categories: ['E-commerce'],
                certified: false,
            },
        ],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} testApps={testApps} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=2')
    ).toBeInTheDocument();
    expect(screen.getByText('First App')).toBeInTheDocument();
    expect(screen.getByText('Second App')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=2')
    ).toBeInTheDocument();
    expect(screen.getByText('First Extension')).toBeInTheDocument();
    expect(screen.getByText('Second Extension')).toBeInTheDocument();

    const searchInput = screen.getByPlaceholderText(
        'akeneo_connectivity.connection.connect.marketplace.search.placeholder'
    ) as HTMLInputElement;

    await act(async () => {
        await userEvent.type(searchInput, 'second', {delay: 0.00001});
    });

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=1')
    ).toBeInTheDocument();
    expect(screen.queryByText('First App')).not.toBeInTheDocument();
    expect(screen.getByText('Second App')).toBeInTheDocument();

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=1')
    ).toBeInTheDocument();
    expect(screen.queryByText('First Extension')).not.toBeInTheDocument();
    expect(screen.getByText('Second Extension')).toBeInTheDocument();
});
