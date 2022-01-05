import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../test-utils';
import {TestAppCreatePage} from '@src/connect/pages/TestAppCreatePage';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The "test app create page" renders and I can cancel', () => {
    renderWithProviders(<TestAppCreatePage />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_app.modal.title')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_app.modal.subtitle')
    ).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.cancel'));

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace');
});
