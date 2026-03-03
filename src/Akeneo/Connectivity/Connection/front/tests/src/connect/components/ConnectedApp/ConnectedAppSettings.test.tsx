import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../../test-utils';

import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {FlowType} from '@src/model/flow-type.enum';
import {SecurityContext} from '@src/shared/security';

jest.mock('@src/connect/components/ConnectedApp/Settings/Authentication', () => ({
    Authentication: () => <div>ConnectedAppAuthentication</div>,
}));

test('Connected App Settings renders monitoring settings and authorizations', () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['scope 1'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
    };

    const monitoringSettings = {
        flowType: FlowType.DATA_DESTINATION,
        auditable: true,
    };

    const handleSetMonitoringSettings = jest.fn(() => {
        return;
    });

    renderWithProviders(
        <ConnectedAppSettings
            connectedApp={connectedApp}
            monitoringSettings={monitoringSettings}
            handleSetMonitoringSettings={handleSetMonitoringSettings}
        />
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.title', {
            exact: false,
        })
    ).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.monitoring.title', {
            exact: false,
        })
    ).toBeInTheDocument();

    expect(screen.queryByText('ConnectedAppAuthentication')).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.title', {
            exact: false,
        })
    ).not.toBeInTheDocument();
});

test('Connected Custom App Settings renders credentials', () => {
    const connectedApp = {
        id: '12345',
        name: 'Custom App A',
        scopes: ['scope 1'],
        connection_code: 'some_connection_code',
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
    };

    const monitoringSettings = {
        flowType: FlowType.DATA_DESTINATION,
        auditable: true,
    };

    const handleSetMonitoringSettings = jest.fn(() => {
        return;
    });

    renderWithProviders(
        <ConnectedAppSettings
            connectedApp={connectedApp}
            monitoringSettings={monitoringSettings}
            handleSetMonitoringSettings={handleSetMonitoringSettings}
        />
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.title', {
            exact: false,
        })
    ).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.monitoring.title', {
            exact: false,
        })
    ).toBeInTheDocument();

    expect(screen.queryByText('ConnectedAppAuthentication')).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.title', {
            exact: false,
        })
    ).toBeInTheDocument();
});
