import '@testing-library/jest-dom/extend-expect';
import React from 'react';
import {MemoryRouter} from 'react-router-dom';
import {ConnectionsProvider} from '../../../../src/settings/connections-context';
import {ListConnections} from '../../../../src/settings/pages/ListConnections';
import {act, render, waitForElement} from '../../../utils/test-utils';

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
            ])
        );

        const {getByText} = render(
            <MemoryRouter>
                <ConnectionsProvider>
                    <ListConnections />
                </ConnectionsProvider>
            </MemoryRouter>
        );

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_list');

        await waitForElement(() => getByText('Franklin'));
    });
});
