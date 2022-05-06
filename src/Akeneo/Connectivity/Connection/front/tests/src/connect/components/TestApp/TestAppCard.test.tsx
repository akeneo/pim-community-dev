import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
import {TestAppCard} from '@src/connect/components/TestApp/TestAppCard';

test('it displays test app', () => {
    const testApp = {
        id: 'id1',
        name: 'Name of the test app',
        logo: null,
        author: 'Author Name',
        url: null,
        activate_url: 'test_app_1_activate_url',
        callback_url: 'test_app_1_callback_url',
        connected: false,
    };
    renderWithProviders(<TestAppCard testApp={testApp} />);

    expect(screen.queryByText('Name of the test app')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by?author=Author+Name')
    ).toBeInTheDocument();
});

test('it displays test app with removed author', () => {
    const testApp = {
        id: 'id1',
        name: 'Name of the test app',
        logo: null,
        author: null,
        url: null,
        activate_url: 'test_app_1_activate_url',
        callback_url: 'test_app_1_callback_url',
        connected: false,
    };
    renderWithProviders(<TestAppCard testApp={testApp} />);

    expect(screen.queryByText('Name of the test app')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.card.developed_by?author=akeneo_connectivity.connection.connect.marketplace.test_apps.removed_user'
        )
    ).toBeInTheDocument();
});
