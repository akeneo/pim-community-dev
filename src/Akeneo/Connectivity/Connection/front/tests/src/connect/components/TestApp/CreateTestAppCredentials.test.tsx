import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {CreateTestAppCredentials} from '@src/connect/components/TestApp/CreateTestAppCredentials';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('it displays the credentials', () => {
    const onClose = jest.fn();
    const setCredentials = jest.fn();
    const credentials = {clientId: '', clientSecret: ''};

    renderWithProviders(
        <CreateTestAppCredentials credentials={credentials} onClose={onClose} setCredentials={setCredentials} />
    );

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.title')
    ).toBeInTheDocument();
});
