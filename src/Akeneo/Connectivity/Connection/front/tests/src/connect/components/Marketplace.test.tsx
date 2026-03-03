import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {act, screen} from '@testing-library/react';
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
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
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
    const customApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
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
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
        },
    });

    const apps = {
        total: 0,
        apps: [],
    };
    const customApps = {
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

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
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

test('The marketplace renders with custom apps', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
        },
    });

    const apps = {
        total: 0,
        apps: [],
    };
    const customApps = {
        total: 1,
        apps: [
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                name: 'Custom App A',
                logo: null,
                author: null,
                url: null,
                activate_url: 'custom_app_a_activate_url',
                callback_url: 'custom_app_a_callback_url',
                connected: false,
            },
        ],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getByText('MarketplaceHelper')).toBeInTheDocument();
    expect(
        screen.getByPlaceholderText('akeneo_connectivity.connection.connect.marketplace.search.placeholder')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
    ).toBeInTheDocument();
    expect(screen.getByText('Custom App A')).toBeInTheDocument();
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

test('The search input filters custom apps, apps and extensions', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
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
    const customApps = {
        total: 2,
        apps: [
            {
                id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                name: 'First Custom App',
                logo: null,
                author: null,
                url: null,
                activate_url: 'custom_app_a_activate_url',
                callback_url: 'custom_app_a_callback_url',
                connected: false,
            },
            {
                id: '313a25ae-ae96-11ed-afa1-0242ac120002',
                name: 'Second Custom App',
                logo: null,
                author: null,
                url: null,
                activate_url: 'custom_app_b_activate_url',
                callback_url: 'custom_app_b_callback_url',
                connected: false,
            },
        ],
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

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getAllByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=2')).toHaveLength(
        2
    );
    expect(screen.getByText('First App')).toBeInTheDocument();
    expect(screen.getByText('Second App')).toBeInTheDocument();

    expect(screen.getByText('First Custom App')).toBeInTheDocument();
    expect(screen.getByText('Second Custom App')).toBeInTheDocument();

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

    expect(screen.getAllByText('akeneo_connectivity.connection.connect.marketplace.apps.total?total=1')).toHaveLength(
        2
    );
    expect(screen.queryByText('First App')).not.toBeInTheDocument();
    expect(screen.getByText('Second App')).toBeInTheDocument();

    expect(screen.queryByText('First Custom App')).not.toBeInTheDocument();
    expect(screen.getByText('Second Custom App')).toBeInTheDocument();

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.total?total=1')
    ).toBeInTheDocument();
    expect(screen.queryByText('First Extension')).not.toBeInTheDocument();
    expect(screen.getByText('Second Extension')).toBeInTheDocument();
});

test('The connect buttons are disabled and a warning is showed when the limit of connected app is reached', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: true,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: true},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
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
    const customApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getByText('App A')).toBeInTheDocument();

    expect(
        await screen.findByText('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect')).toBeInTheDocument();

    const connectButton = expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect'));

    connectButton.toHaveAttribute('disabled');
    connectButton.toHaveAttribute('aria-disabled', 'true');
});

test('The connect buttons are disabled and a warning is showed when the user cannot manage apps', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: (feature: string) =>
            ({
                marketplace_activate: true,
            }[feature] ?? false),
    }));
    (useSecurity as jest.Mock).mockImplementation(() => ({
        isGranted: (acl: string) =>
            ({
                akeneo_connectivity_connection_manage_apps: false,
            }[acl] ?? false),
    }));

    mockFetchResponses({
        akeneo_connectivity_connection_rest_connections_max_limit_reached: {
            json: {limitReached: false},
        },
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: false,
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
    const customApps = {
        total: 0,
        apps: [],
    };
    const extensions = {
        total: 0,
        extensions: [],
    };

    renderWithProviders(<Marketplace apps={apps} extensions={extensions} customApps={customApps} />);

    expect(screen.getByText('App A')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect')).toBeInTheDocument();

    const connectButton = expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect'));

    connectButton.toHaveAttribute('disabled');
    connectButton.toHaveAttribute('aria-disabled', 'true');
});
