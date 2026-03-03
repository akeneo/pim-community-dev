import '@testing-library/jest-dom/extend-expect';
import {waitFor} from '@testing-library/react';
import React from 'react';
import {MemoryRouter} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {ListConnections} from '@src/settings/pages/ListConnections';
import {renderWithProviders} from '../../../test-utils';
import {WrongCredentialsCombinationsProvider} from '@src/settings/wrong-credentials-combinations-context';

jest.mock('@src/shared/hooks/use-connections-limit-reached', () => ({
    ...jest.requireActual('@src/shared/hooks/use-connections-limit-reached'),
    useConnectionsLimitReached: jest.fn(() => {
        return false;
    }),
}));

describe('testing ListConnections page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('list connections', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify([
                {
                    code: 'franklin',
                    label: 'Franklin',
                    flowType: 'data_source',
                    image: null,
                },
                {
                    code: 'dam',
                    label: 'DAM',
                    flowType: 'data_source',
                    image: null,
                },
            ])
        );
        fetchMock.mockResponseOnce(
            JSON.stringify({
                dam: {
                    code: 'dam',
                    users: [
                        {
                            username: 'not_dam',
                            date: '2020-01-02 12:34:23',
                        },
                    ],
                },
            })
        );

        const {getByText} = renderWithProviders(
            <MemoryRouter>
                <WrongCredentialsCombinationsProvider>
                    <ConnectionsProvider>
                        <ListConnections />
                    </ConnectionsProvider>
                </WrongCredentialsCombinationsProvider>
            </MemoryRouter>
        );

        expect(fetchMock).toBeCalledTimes(2);
        expect(fetchMock.mock.calls[0][0]).toEqual(
            'akeneo_connectivity_connection_rest_list?search=%7B%22types%22%3A%5B%22default%22%5D%7D'
        );
        expect(fetchMock.mock.calls[1][0]).toEqual(
            'akeneo_connectivity_connection_rest_wrong_credentials_combination_list'
        );

        await waitFor(() => [
            getByText('Franklin'),
            getByText('DAM'),
            getByText('akeneo_connectivity.connection.flow_type.data_source'),
            getByText('akeneo_connectivity.connection.connection_count?count=2'),
        ]);
    });
});
