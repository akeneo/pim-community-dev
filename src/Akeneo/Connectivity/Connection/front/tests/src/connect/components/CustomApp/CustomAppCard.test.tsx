import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
import {CustomAppCard} from '@src/connect/components/CustomApps/CustomAppCard';
import {SecurityContext} from '@src/shared/security';

test('it displays custom app', () => {
    const customApp = {
        id: 'id1',
        name: 'Name of the custom app',
        logo: null,
        author: 'Author Name',
        url: null,
        activate_url: 'custom_app_1_activate_url',
        callback_url: 'custom_app_1_callback_url',
        connected: false,
    };
    renderWithProviders(<CustomAppCard customApp={customApp} />);

    expect(screen.queryByText('Name of the custom app')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.developed_by?author=Author+Name')
    ).toBeInTheDocument();
});

test('it displays custom app with removed author', () => {
    const customApp = {
        id: 'id1',
        name: 'Name of the custom app',
        logo: null,
        author: null,
        url: null,
        activate_url: 'custom_app_1_activate_url',
        callback_url: 'custom_app_1_callback_url',
        connected: false,
    };
    renderWithProviders(<CustomAppCard customApp={customApp} />);

    expect(screen.queryByText('Name of the custom app')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.card.developed_by?author=akeneo_connectivity.connection.connect.marketplace.custom_apps.removed_user'
        )
    ).toBeInTheDocument();
});

test('it not displays the delete button when the user doesnt have the permission to delete custom Apps', () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_test_apps') {
            return false;
        }
        return true;
    });

    const customApp = {
        id: 'id1',
        name: 'Name of the custom app',
        logo: null,
        author: null,
        url: null,
        activate_url: 'custom_app_1_activate_url',
        callback_url: 'custom_app_1_callback_url',
        connected: false,
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <CustomAppCard customApp={customApp} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('Name of the custom app')).toBeInTheDocument();

    expect(screen.queryByTitle('pim_common.delete')).not.toBeInTheDocument();
});
