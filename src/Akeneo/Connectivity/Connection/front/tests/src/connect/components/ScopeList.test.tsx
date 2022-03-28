import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, renderWithProviders} from '../../../test-utils';
import {screen} from '@testing-library/react';
import {ScopeList} from '@src/connect/components/ScopeList';

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

    renderWithProviders(<ScopeList scopeMessages={scopes} />);

    expect(
        screen.getByTitle('akeneo_connectivity.connection.connect.apps.scope.entities.products')
    ).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.scope.entities.products')).toBeInTheDocument();
});

test('The scope list still renders with unknown scopes', () => {
    const scopes = [
        {
            icon: 'foo',
            type: 'read',
            entities: 'foo',
        },
    ];

    renderWithProviders(<ScopeList scopeMessages={scopes} />);

    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.scope.entities.foo')).toBeInTheDocument();
});

test('The scope list renders without scopes', () => {
    renderWithProviders(<ScopeList scopeMessages={[]} />);

    expect(screen.getByTestId('scope-list')).toBeInTheDocument();
    expect(screen.getByTestId('scope-list')).toBeEmptyDOMElement();
});

test('The scope list renders with NEW badge', () => {
    const scopes = [
        {
            icon: 'foo',
            type: 'read',
            entities: 'foo',
        },
    ];

    renderWithProviders(<ScopeList scopeMessages={scopes} highlightMode={'new'} />);

    expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.new')).toBeInTheDocument();
});
