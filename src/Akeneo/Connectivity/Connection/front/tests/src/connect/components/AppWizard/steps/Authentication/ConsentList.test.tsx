import {ConsentList} from '@src/connect/components/AppWizard/steps/Authentication/ConsentList';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import React from 'react';
import {renderWithProviders} from '../../../../../../test-utils';

test('it displays both profile & email scopes', () => {
    renderWithProviders(<ConsentList scopes={['profile', 'email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.new')).not.toBeInTheDocument();
});

test('it displays only the profile scope', () => {
    renderWithProviders(<ConsentList scopes={['profile']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.new')).not.toBeInTheDocument();
});

test('it displays only the email scope', () => {
    renderWithProviders(<ConsentList scopes={['email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.new')).not.toBeInTheDocument();
});

test('it displays the NEW badge', () => {
    renderWithProviders(<ConsentList scopes={['email']} highlightMode={'new'} />);

    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.new')).toBeInTheDocument();
});
