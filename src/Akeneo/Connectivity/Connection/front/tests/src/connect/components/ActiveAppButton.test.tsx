import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {historyMock, renderWithProviders} from '../../../test-utils';
import {ActivateAppButton} from '@src/connect/components/ActivateAppButton';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('button renders for connected app', () => {
    renderWithProviders(<ActivateAppButton id='appId' isConnected={true} isDisabled={false} />);

    const button = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connected');

    expect(button).toBeInTheDocument();
    expect(button).toHaveAttribute('disabled');
});

test('button renders for non connected app', () => {
    renderWithProviders(<ActivateAppButton id='appId' isConnected={false} isDisabled={false} />);

    const button = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connect');

    expect(button).toBeInTheDocument();
    expect(button).toHaveAttribute('href', '#akeneo_connectivity_connection_connect_apps_activate?id=appId');
    expect(button).not.toHaveAttribute('disabled');
});

test('button renders disabled for non connected app when limit is reached', () => {
    renderWithProviders(<ActivateAppButton id='appId' isConnected={false} isLimitReached={true} />);

    const button = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connect');

    expect(button).toBeInTheDocument();
    expect(button).toHaveAttribute('disabled');
});
