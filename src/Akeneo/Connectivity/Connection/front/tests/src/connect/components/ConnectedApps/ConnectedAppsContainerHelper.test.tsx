import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import ConnectedAppsContainerHelper from '@src/connect/components/ConnectedApps/ConnectedAppsContainerHelper';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The connected apps list helper renders', async () => {
    renderWithProviders(<ConnectedAppsContainerHelper count={2} />);
    await waitFor(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.title', {exact: false})
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_1')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_2')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.link')
    ).toBeInTheDocument();
});
