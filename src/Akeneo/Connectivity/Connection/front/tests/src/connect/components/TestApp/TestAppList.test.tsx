import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
import {TestAppCard} from '@src/connect/components/TestApp/TestAppCard';
import {TestAppList} from '@src/connect/components/TestApp/TestAppList';
import {useFeatureFlags} from '@src/shared/feature-flags';

const emptyTestApps = {total: 0, apps: []};

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

test('it displays test app', () => {
    const testApps = {
        total: 2,
        apps: [
            {
                id: 'id1',
                name: 'testApp1',
                author: 'AuthorName',
                activate_url: 'test_app_1_activate_url',
                callback_url: 'test_app_1_callback_url',
                connected: false,
            },
            {
                id: 'id2',
                name: 'testApp2',
                author: null,
                activate_url: 'test_app_2_activate_url',
                callback_url: 'test_app_2_callback_url',
                connected: true,
            },
        ],
    };
    renderWithProviders(<TestAppList testApps={testApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(screen.queryByText('testApp1')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by AuthorName')
    ).toBeInTheDocument();
    expect(screen.queryByText('testApp2')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by pim_user.removed_user')
    ).toBeInTheDocument();
});

test('it displays nothing when total is 0', () => {
    const testApps = {
        total: 0,
        apps: [
            {
                id: 'id1',
                name: 'testApp1',
                author: 'AuthorName',
                activate_url: 'test_app_1_activate_url',
                callback_url: 'test_app_1_callback_url',
                connected: false,
            },
            {
                id: 'id2',
                name: 'testApp2',
                author: null,
                activate_url: 'test_app_2_activate_url',
                callback_url: 'test_app_2_callback_url',
                connected: true,
            },
        ],
    };
    renderWithProviders(<TestAppList testApps={testApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).not.toBeInTheDocument();

    expect(screen.queryByText('testApp1')).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by AuthorName')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('testApp2')).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by pim_user.removed_user')
    ).not.toBeInTheDocument();
});

test('it displays nothing when feature flag is disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({isEnabled: () => false}));
    const testApps = {
        total: 2,
        apps: [
            {
                id: 'id1',
                name: 'testApp1',
                author: 'AuthorName',
                activate_url: 'test_app_1_activate_url',
                callback_url: 'test_app_1_callback_url',
                connected: false,
            },
            {
                id: 'id2',
                name: 'testApp2',
                author: null,
                activate_url: 'test_app_2_activate_url',
                callback_url: 'test_app_2_callback_url',
                connected: true,
            },
        ],
    };
    renderWithProviders(<TestAppList testApps={testApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).not.toBeInTheDocument();

    expect(screen.queryByText('testApp1')).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by AuthorName')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('testApp2')).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by pim_user.removed_user')
    ).not.toBeInTheDocument();
});
