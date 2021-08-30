import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ScopeList} from '@src/connect/components/AppWizard/ScopeList';

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

    renderWithProviders(<ScopeList appName='MyApp' scopeMessages={scopes} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.getByTitle('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.products')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.products')
    ).toBeInTheDocument();
});

test('The scope list still renders with unknown scopes', () => {
    const scopes = [
        {
            icon: 'foo',
            type: 'read',
            entities: 'foo',
        },
    ];

    renderWithProviders(<ScopeList appName='MyApp' scopeMessages={scopes} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.foo')
    ).toBeInTheDocument();
});

test('The scope list renders without scopes', () => {
    renderWithProviders(<ScopeList appName='MyApp' scopeMessages={[]} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.no_scope_title', {exact: false})
    ).toBeInTheDocument();
    expect(screen.getByTitle('akeneo_connectivity.connection.connect.apps.authorize.no_scope')).toBeInTheDocument();
});
