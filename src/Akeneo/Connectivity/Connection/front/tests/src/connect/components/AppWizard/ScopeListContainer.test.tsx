import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ScopeListContainer} from '@src/connect/components/AppWizard/ScopeListContainer';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The scope list renders with scopes', () => {
    const scopes = [
        {
            icon: 'products',
            type: 'read',
            entities: 'products',
        },
    ];

    renderWithProviders(<ScopeListContainer appName='MyApp' scopeMessages={scopes} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.title', {exact: false})
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')).toBeInTheDocument();
    expect(screen.getByTestId('scope-list')).toBeInTheDocument();
});

test('The scope list still renders with unknown scopes', () => {
    const scopes = [
        {
            icon: 'foo',
            type: 'read',
            entities: 'foo',
        },
    ];

    renderWithProviders(<ScopeListContainer appName='MyApp' scopeMessages={scopes} />);

    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')).toBeInTheDocument();
    expect(screen.getByTestId('scope-list')).toBeInTheDocument();
});

test('The scope list renders without scopes', () => {
    renderWithProviders(<ScopeListContainer appName='MyApp' scopeMessages={[]} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope_title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.getByTitle('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope')
    ).toBeInTheDocument();
    expect(screen.queryByTestId('scope-list')).not.toBeInTheDocument();
});
