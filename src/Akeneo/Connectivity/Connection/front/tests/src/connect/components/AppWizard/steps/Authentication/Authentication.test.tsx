import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import React from 'react';
import {historyMock, renderWithProviders} from '../../../../../../test-utils';
import {ConsentList} from '@src/connect/components/AppWizard/steps/Authentication/ConsentList';
import fetchMock from 'jest-fetch-mock';

jest.mock('@src/connect/components/AppWizard/steps/Authentication/UserAvatar', () => ({
    UserAvatar: () => <div>UserAvatar</div>,
}));
jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentList', () => ({
    ConsentList: jest.fn(() => <div>ConsentList</div>),
}));
jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentCheckbox', () => ({
    ConsentCheckbox: () => <div>ConsentCheckbox</div>,
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('it renders correctly for first connection without consent checkbox', async () => {
    renderWithProviders(
        <Authentication
            appName='MyApp'
            scopes={['email']}
            oldScopes={null}
            appUrl={null}
            scopesConsentGiven={false}
            setScopesConsent={() => null}
            displayConsent={false}
            displayCheckboxConsent={true}
        />
    );

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(ConsentList).toHaveBeenCalledTimes(1);
    expect(ConsentList).toHaveBeenCalledWith({scopes: ['email'], highlightMode: null}, {});
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).not.toBeInTheDocument();
});

test('it renders correctly for first connection with consent checkbox', async () => {
    renderWithProviders(
        <Authentication
            appName='MyApp'
            scopes={['email']}
            oldScopes={null}
            appUrl={null}
            scopesConsentGiven={false}
            setScopesConsent={() => null}
            displayConsent={true}
            displayCheckboxConsent={true}
        />
    );

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(ConsentList).toHaveBeenCalledTimes(1);
    expect(ConsentList).toHaveBeenCalledWith({scopes: ['email'], highlightMode: null}, {});
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).toBeInTheDocument();
});

test('it renders correctly with new scopes required when not already had old scopes', async () => {
    renderWithProviders(
        <Authentication
            appName='MyApp'
            scopes={['email']}
            oldScopes={[]}
            appUrl={null}
            scopesConsentGiven={false}
            setScopesConsent={() => null}
            displayConsent={false}
            displayCheckboxConsent={true}
        />
    );

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(ConsentList).toHaveBeenCalledTimes(1);
    expect(ConsentList).toHaveBeenCalledWith({scopes: ['email'], highlightMode: 'new'}, {});
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to')
    ).not.toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).not.toBeInTheDocument();
});

test('it renders correctly with new scopes required when already accepted old scopes', async () => {
    renderWithProviders(
        <Authentication
            appName='MyApp'
            scopes={['email']}
            oldScopes={['profile']}
            appUrl={null}
            scopesConsentGiven={false}
            setScopesConsent={() => null}
            displayConsent={false}
            displayCheckboxConsent={true}
        />
    );

    await waitFor(() =>
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.title?app_name=MyApp')
    ).toBeInTheDocument();
    expect(screen.queryByText('UserAvatar')).toBeInTheDocument();
    expect(ConsentList).toHaveBeenCalledTimes(2);
    expect(ConsentList).toHaveBeenNthCalledWith(1, {scopes: ['email'], highlightMode: 'new'}, {});
    expect(ConsentList).toHaveBeenNthCalledWith(2, {scopes: ['profile'], highlightMode: 'old'}, {});
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to')
    ).toBeInTheDocument();
    expect(screen.queryByText('ConsentCheckbox')).not.toBeInTheDocument();
});
