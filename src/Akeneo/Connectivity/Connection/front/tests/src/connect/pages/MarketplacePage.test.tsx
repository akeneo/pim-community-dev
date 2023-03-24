import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {historyMock, renderWithProviders} from '../../../test-utils';
import {screen, waitFor} from '@testing-library/react';
import {MarketplacePage} from '@src/connect/pages/MarketplacePage';
import {Marketplace} from '@src/connect/components/Marketplace';
import userEvent from '@testing-library/user-event';
import {SecurityContext} from '@src/shared/security';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    useFeatureFlags: jest.fn().mockImplementation(() => ({
        isEnabled: () => true,
    })),
}));
jest.mock('@src/connect/hooks/use-fetch-extensions', () => ({
    useFetchExtensions: jest.fn().mockImplementation(() => () => Promise.resolve([])),
}));
jest.mock('@src/connect/hooks/use-fetch-apps', () => ({
    useFetchApps: jest.fn().mockImplementation(() => () => Promise.resolve([])),
}));
jest.mock('@src/connect/components/Marketplace', () => ({
    Marketplace: jest.fn().mockImplementation(() => null),
}));
jest.mock('@src/connect/hooks/use-custom-apps-limit-reached', () => ({
    useCustomAppsLimitReached: jest.fn().mockImplementation(() => false),
}));

test('It displays "create an app" button when user can manage a custom app', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_test_apps') {
            return true;
        }
        return false;
    });

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <MarketplacePage />
        </SecurityContext.Provider>
    );

    await waitFor(() => expect(Marketplace).toHaveBeenCalled());

    expect(screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_button')).toBeInTheDocument();
});

test('It hides "create an app" button when user cannot manage a custom app', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_test_apps') {
            return false;
        }
        return false;
    });

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <MarketplacePage />
        </SecurityContext.Provider>
    );

    await waitFor(() => expect(Marketplace).toHaveBeenCalled());

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_button')
    ).not.toBeInTheDocument();
});

test('It redirects when the "Create an app" button is clicked', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_test_apps') {
            return true;
        }
        return false;
    });

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <MarketplacePage />
        </SecurityContext.Provider>
    );

    await waitFor(() => expect(Marketplace).toHaveBeenCalled());
    userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_button'));

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_custom_apps_create');
});
