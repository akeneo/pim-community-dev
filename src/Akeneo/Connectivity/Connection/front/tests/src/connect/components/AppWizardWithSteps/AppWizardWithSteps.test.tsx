import React, {useEffect} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitForElement} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {historyMock, mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {AppWizardWithSteps, PermissionsType} from '@src/connect/components/AppWizardWithSteps/AppWizardWithSteps';
import {PermissionFormProvider} from '@src/shared/permission-form-registry';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('@src/connect/components/AppWizardWithSteps/Authorizations', () => ({
    Authorizations: () => <div>authorizations-component</div>,
}));

type PermissionsProps = {
    appName: string;
    providers: PermissionFormProvider<any>[];
    setPermissions: (state: PermissionsType) => void;
    permissions: PermissionsType;
};
jest.mock('@src/connect/components/AppWizardWithSteps/Permissions', () => ({
    Permissions: ({permissions, setPermissions}: PermissionsProps) => {
        const handleClick = () => {
            setPermissions({data: 'hello world!'});
        };

        return (
            <div data-testid='set-permissions' onClick={handleClick}>
                permissions-component {permissions.data}
            </div>
        );
    },
}));
type SummaryProps = {
    permissions: PermissionsType;
};
jest.mock('@src/connect/components/AppWizardWithSteps/PermissionsSummary', () => ({
    PermissionsSummary: ({permissions}: SummaryProps) => <div>permissions-summary-component {permissions.data}</div>,
}));
test('The step wizard renders without error', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));
    expect(screen.queryByAltText('MyApp')).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component')).not.toBeInTheDocument();
});

test('The wizard redirect to the marketplace when closed', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByTitle('akeneo_connectivity.connection.connect.apps.wizard.action.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/connect/marketplace');
});

test('The wizard renders steps and is able to navigate between steps', async () => {
    const fetchAppWizardDataResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=8d8a7dc1-0827-4cc9-9ae5-577c6419230b': {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });
    renderWithProviders(<AppWizardWithSteps clientId='8d8a7dc1-0827-4cc9-9ae5-577c6419230b' />);
    await waitForElement(() => screen.getByAltText('MyApp'));

    assertAuthorizationsScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });
    assertPermissionsScreen();
    expect(screen.queryByText('permissions-component')).toBeInTheDocument();
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.next'));
    });
    assertPermissionsSummaryScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous'));
    });
    assertPermissionsScreen();
    expect(screen.queryByText('permissions-component hello world!')).toBeInTheDocument();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous'));
    });
    assertAuthorizationsScreen();
});

const assertAuthorizationsScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component', {exact: false})).not.toBeInTheDocument();
};

const assertPermissionsScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component', {exact: false})).not.toBeInTheDocument();
};

const assertPermissionsSummaryScreen = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.next')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.action.previous')
    ).toBeInTheDocument();
    expect(screen.queryByText('authorizations-component')).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-component', {exact: false})).not.toBeInTheDocument();
    expect(screen.queryByText('permissions-summary-component hello world!')).toBeInTheDocument();
};
