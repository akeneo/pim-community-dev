import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
import {TestAppList} from '@src/connect/components/TestApp/TestAppList';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {TestAppCard} from '@src/connect/components/TestApp/TestAppCard';

beforeEach(() => {
    jest.clearAllMocks();
});

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

jest.mock('@src/connect/components/TestApp/TestAppCard', () => ({
    ...jest.requireActual('@src/connect/components/TestApp/TestAppCard'),
    TestAppCard: jest.fn(() => null),
}));

const testApp1 = {
    id: 'id1',
    name: 'testApp1',
    logo: null,
    author: 'AuthorName',
    url: null,
    activate_url: 'test_app_1_activate_url',
    callback_url: 'test_app_1_callback_url',
    connected: false,
};

const testApp2 = {
    id: 'id2',
    name: 'testApp2',
    logo: null,
    author: null,
    url: null,
    activate_url: 'test_app_2_activate_url',
    callback_url: 'test_app_2_callback_url',
    connected: true,
};

test('it displays test app', () => {
    const testApps = {
        total: 2,
        apps: [testApp1, testApp2],
    };
    renderWithProviders(<TestAppList testApps={testApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(TestAppCard).toHaveBeenNthCalledWith(
        1,
        {
            testApp: testApp1,
            additionalActions: expect.anything(),
        },
        {}
    );

    expect(TestAppCard).toHaveBeenNthCalledWith(
        2,
        {
            testApp: testApp2,
            additionalActions: expect.anything(),
        },
        {}
    );
});

test('it displays nothing when total is 0', () => {
    const testApps = {
        total: 0,
        apps: [testApp1, testApp2],
    };
    renderWithProviders(<TestAppList testApps={testApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();

    expect(TestAppCard).not.toHaveBeenCalled();
});
