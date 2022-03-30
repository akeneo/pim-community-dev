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
    renderWithProviders(<ActivateAppButton id='appId' isConnected={true} isPending={false} isDisabled={false} />);

    const button = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connected');
    const pendingButton = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.pending');

    expect(pendingButton).not.toBeInTheDocument();
    expect(button).toBeInTheDocument();
    expect(button).toHaveAttribute('disabled');
});

test('button renders for non connected app', () => {
    renderWithProviders(<ActivateAppButton id='appId' isConnected={false} isPending={false} isDisabled={false} />);

    const button = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connect');
    const pendingButton = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.pending');

    expect(pendingButton).not.toBeInTheDocument();
    expect(button).toBeInTheDocument();
    expect(button).toHaveAttribute('href', '#akeneo_connectivity_connection_connect_apps_activate?id=appId');
    expect(button).not.toHaveAttribute('disabled');
});

test('button renders for pending app', () => {
    renderWithProviders(<ActivateAppButton id='appId' isConnected={false} isPending={true} isDisabled={false} />);

    const connectButton = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.connect');
    const pendingButton = screen.queryByText('akeneo_connectivity.connection.connect.marketplace.card.pending');

    expect(connectButton).not.toBeInTheDocument();
    expect(pendingButton).toBeInTheDocument();
    expect(pendingButton).toHaveAttribute('disabled');
});
