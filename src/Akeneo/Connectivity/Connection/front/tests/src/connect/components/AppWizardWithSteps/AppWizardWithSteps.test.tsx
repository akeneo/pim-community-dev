import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, wait, waitForElement} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {historyMock, mockFetchResponses, MockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {AppWizardWithSteps} from '@src/connect/components/AppWizardWithSteps/AppWizardWithSteps';
import {PermissionFormProvider, PermissionFormRegistryContext} from '@src/shared/permission-form-registry';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {PermissionsByProviderKey} from '@src/model/Apps/permissions-by-provider-key';

const notify = jest.fn();
const providerSave = jest.fn();
const providerLoadPermissions = jest.fn();

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    notify.mockClear();
    providerSave.mockClear();
    providerLoadPermissions.mockClear();
});

jest.mock('@src/connect/components/AppWizardWithSteps/Authorizations', () => ({
    Authorizations: () => <div>authorizations-component</div>,
}));

type PermissionsProps = {
    appName: string;
    providers: PermissionFormProvider<any>[];
    setPermissions: (state: PermissionsByProviderKey) => void;
    permissions: PermissionsByProviderKey;
};
jest.mock('@src/connect/components/AppWizardWithSteps/Permissions', () => ({
    Permissions: ({permissions, setPermissions}: PermissionsProps) => {
        const handleClick = () => {
            setPermissions({
                data: 'hello world!',
                formProvider1: 'formProviderData1',
                formProvider2: 'formProviderData2',
                formProvider3: 'formProviderData3',
                formProvider4: 'formProviderData4',
            });
        };

        return (
            <div data-testid='set-permissions' onClick={handleClick}>
                permissions-component {permissions.data}
            </div>
        );
    },
}));
type SummaryProps = {
    permissions: PermissionsByProviderKey;
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

test('The wizard notifies of the error on app confirm ', async () => {
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const fetchAppWizardDataResponses: MockFetchResponses = {
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            status: 400,
            json: '',
        },
    };

    mockFetchResponses({
        ...fetchAppWizardDataResponses,
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <AppWizardWithSteps clientId={clientId} />
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await wait(() => expect(notify).toHaveBeenCalledTimes(1));

    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.error'
    );
});

test('The wizard saves app and permissions on confirm', async () => {
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            json: {
                userGroup: appUserGroup,
            },
        },
    });

    const providers = [
        {
            key: 'formProvider1',
            label: 'formProviderLabel1',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
        {
            key: 'formProvider2',
            label: 'formProviderLabel2',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
    ];
    const registry = {
        all: () => Promise.resolve(providers),
        countProviders: () => providers.length,
    };

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <PermissionFormRegistryContext.Provider value={registry}>
                <AppWizardWithSteps clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await wait(() => {
        expect(notify).toHaveBeenCalledTimes(1);
        expect(providerSave).toHaveBeenCalledTimes(2);
    });

    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );

    expect(providerSave).toHaveBeenNthCalledWith(1, appUserGroup, 'formProviderData1');
    expect(providerSave).toHaveBeenNthCalledWith(2, appUserGroup, 'formProviderData2');
});

test('The wizard saves app but have some failing permissions on confirm', async () => {
    const clientId = '8d8a7dc1-0827-4cc9-9ae5-577c6419230b';
    const appUserGroup = 'AppUserGroup';

    mockFetchResponses({
        [`akeneo_connectivity_connection_apps_rest_get_wizard_data?clientId=${clientId}`]: {
            json: {
                appName: 'MyApp',
                appLogo: '',
                scopeMessages: [],
            },
        },
        [`akeneo_connectivity_connection_apps_rest_confirm_authorization?clientId=${clientId}`]: {
            json: {
                userGroup: appUserGroup,
            },
        },
    });

    providerSave
        .mockResolvedValue(undefined)
        .mockResolvedValueOnce(undefined)
        .mockResolvedValueOnce(undefined)
        .mockRejectedValueOnce(undefined)
        .mockImplementationOnce(() => {
            throw new Error('testError');
        });

    const providers = [
        {
            key: 'formProvider1',
            label: 'formProviderLabel1',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
        {
            key: 'formProvider2',
            label: 'formProviderLabel2',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
        {
            key: 'formProvider3',
            label: 'formProviderLabel3',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
        {
            key: 'formProvider4',
            label: 'formProviderLabel4',
            renderForm: () => null,
            renderSummary: () => null,
            save: providerSave,
            loadPermissions: providerLoadPermissions,
        },
    ];
    const registry = {
        all: () => Promise.resolve(providers),
        countProviders: () => providers.length,
    };

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <PermissionFormRegistryContext.Provider value={registry}>
                <AppWizardWithSteps clientId={clientId} />
            </PermissionFormRegistryContext.Provider>
        </NotifyContext.Provider>
    );

    await navigateToSummaryAndClickConfirm();

    await wait(() => {
        expect(notify).toHaveBeenCalledTimes(3);
        expect(providerSave).toHaveBeenCalledTimes(4);
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title?entity=formProviderLabel3',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        2,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title?entity=formProviderLabel4',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        3,
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );

    expect(providerSave).toHaveBeenNthCalledWith(1, appUserGroup, 'formProviderData1');
    expect(providerSave).toHaveBeenNthCalledWith(2, appUserGroup, 'formProviderData2');
    expect(providerSave).toHaveBeenNthCalledWith(3, appUserGroup, 'formProviderData3');
    expect(providerSave).toHaveBeenNthCalledWith(4, appUserGroup, 'formProviderData4');
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

const navigateToSummaryAndClickConfirm = async () => {
    await waitForElement(() => screen.getByAltText('MyApp'));

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next'));
    });

    expect(screen.queryByText('permissions-component')).toBeInTheDocument();
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.next'));
    });
    assertPermissionsSummaryScreen();

    act(() => {
        userEvent.click(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.action.confirm'));
    });
};
