import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, MockFetchResponses, renderWithProviders, historyMock} from '../../../../test-utils';
import {ScopeList} from '@src/connect/components/AppWizard/ScopeList';
import userEvent from '@testing-library/user-event';
import {ScopeMessage} from '@src/connect/hooks/use-fetch-app-wizard-data';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The scope list renders with scopes', async () => {
    const scopes = [
        {
            icon: "products",
            type: "read",
            entities: "products",
        },
    ];

    renderWithProviders(
        <ScopeList
            appName="MyApp"
            scopeMessages={scopes}
        />
    );

    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.title', {exact: false})).toBeInTheDocument();
    expect(screen.getByTitle('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.products')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.products')).toBeInTheDocument();
});

test('The scope list still renders with unknown scopes', async () => {
    const scopes = [
        {
            icon: "foo",
            type: "read",
            entities: "foo",
        },
    ];

    renderWithProviders(
        <ScopeList
            appName="MyApp"
            scopeMessages={scopes}
        />
    );

    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.scope.entities.foo')).toBeInTheDocument();
});

test('The scope list renders without scopes', async () => {
    const scopes: ScopeMessage[] = [];

    renderWithProviders(
        <ScopeList
            appName="MyApp"
            scopeMessages={scopes}
        />
    );

    expect(screen.getByText('akeneo_connectivity.connection.connect.apps.authorize.no_scope_title', {exact: false})).toBeInTheDocument();
    expect(screen.getByTitle('akeneo_connectivity.connection.connect.apps.authorize.no_scope')).toBeInTheDocument();
});
