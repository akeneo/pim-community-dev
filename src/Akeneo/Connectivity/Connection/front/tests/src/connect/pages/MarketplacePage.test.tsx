import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {renderWithProviders} from '../../../test-utils';
import {screen, waitFor} from '@testing-library/react';
import {MarketplacePage} from '@src/connect/pages/MarketplacePage';
import {Marketplace} from '@src/connect/components/Marketplace';

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

test('The marketplace page display the developer mode tag when enabled', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => true,
    }));

    renderWithProviders(<MarketplacePage />);

    await waitFor(() => expect(Marketplace).toHaveBeenCalled());
    expect(screen.queryByText('akeneo_connectivity.connection.developer_mode')).toBeInTheDocument();
});

test('The marketplace page do not display the developer mode tag when not enabled', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => false,
    }));

    renderWithProviders(<MarketplacePage />);

    await waitFor(() => expect(Marketplace).toHaveBeenCalled());
    expect(screen.queryByText('akeneo_connectivity.connection.developer_mode')).not.toBeInTheDocument();
});
