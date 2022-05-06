import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, renderWithProviders} from '../../../test-utils';
import {screen, waitFor} from '@testing-library/react';
import {AppAuthenticatePage} from '@src/connect/pages/AppAuthenticatePage';
import {AuthenticationModal} from '@src/connect/components/AppWizard/AuthenticationModal';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('@src/connect/components/AppWizard/AuthenticationModal', () => ({
    ...jest.requireActual('@src/connect/components/AppWizard/AuthenticationModal'),
    AuthenticationModal: jest.fn(() => <div>AuthenticationModal</div>),
}));

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useLocation: jest.fn().mockImplementation(() => ({
        search: '?client_id=8d8a7dc1-0827-4cc9-9ae5-577c6419230b',
    })),
}));

test('The page renders app authenticate modal', async () => {
    renderWithProviders(<AppAuthenticatePage />);
    await waitFor(() => screen.getByText('AuthenticationModal'));

    expect(screen.queryByText('AuthenticationModal')).toBeInTheDocument();
    expect(AuthenticationModal).toBeCalledWith({clientId: '8d8a7dc1-0827-4cc9-9ae5-577c6419230b'}, {});
});
