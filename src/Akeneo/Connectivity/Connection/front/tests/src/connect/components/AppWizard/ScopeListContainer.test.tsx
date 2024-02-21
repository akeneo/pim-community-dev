import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ScopeListContainer} from '@src/connect/components/AppWizard/ScopeListContainer';
import {ScopeList} from '@src/connect/components/ScopeList';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

jest.mock('@src/connect/components/ScopeList', () => ({
    ...jest.requireActual('@src/connect/components/ScopeList'),
    ScopeList: jest.fn(() => null),
}));

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
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')
    ).toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith({scopeMessages: scopes, highlightMode: null}, {});
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

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')
    ).toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith({scopeMessages: scopes, highlightMode: null}, {});
});

test('The scope list renders without scopes', () => {
    renderWithProviders(<ScopeListContainer appName='MyApp' scopeMessages={[]} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope_title', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(ScopeList).not.toHaveBeenCalled();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope')
    ).toBeInTheDocument();
});

test('The scope list renders with oldScopeMessages', () => {
    const oldScopeMessages = [
        {
            icon: 'products',
            type: 'read',
            entities: 'products',
        },
    ];

    const scopeMessages = [
        {
            icon: 'foo',
            type: 'read',
            entities: 'foo',
        },
    ];

    renderWithProviders(
        <ScopeListContainer appName='MyApp' scopeMessages={scopeMessages} oldScopeMessages={oldScopeMessages} />
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to')
    ).toBeInTheDocument();

    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: scopeMessages,
            highlightMode: 'new',
        },
        {}
    );

    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: oldScopeMessages,
            highlightMode: 'old',
        },
        {}
    );
});
