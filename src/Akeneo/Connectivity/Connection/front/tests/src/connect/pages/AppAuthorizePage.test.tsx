import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {historyMock, renderWithProviders} from '../../../test-utils';
import {screen, waitFor} from '@testing-library/react';
import {AppAuthorizePage} from '@src/connect/pages/AppAuthorizePage';
import {FeatureFlagsContext} from '@src/shared/feature-flags';
import {useLocation} from 'react-router-dom';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('@src/connect/components/AppWizard/AppWizard', () => ({
    AppWizard: () => <div>AppWizard</div>,
}));
jest.mock('@src/connect/components/AuthorizeClientError', () => ({
    AuthorizeClientError: () => <div>AuthorizeClientError</div>,
}));
jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useLocation: jest.fn().mockImplementation(() => ({
        search: '?client_id=8d8a7dc1-0827-4cc9-9ae5-577c6419230b',
    })),
}));

test('The wizard renders app wizard', async () => {
    renderWithProviders(<AppAuthorizePage />);
    await waitFor(() => screen.getByText('AppWizard'));

    expect(screen.queryByText('AppWizard')).toBeInTheDocument();
    expect(screen.queryByText('AuthorizeClientError')).not.toBeInTheDocument();
});

test('The wizard renders client error', async () => {
    (useLocation as jest.Mock).mockImplementationOnce(() => ({
        search: '?error=toto',
    }));
    renderWithProviders(<AppAuthorizePage />);
    await waitFor(() => screen.getByText('AuthorizeClientError'));

    expect(screen.queryByText('AuthorizeClientError')).toBeInTheDocument();
    expect(screen.queryByText('AppWizard')).not.toBeInTheDocument();
});
