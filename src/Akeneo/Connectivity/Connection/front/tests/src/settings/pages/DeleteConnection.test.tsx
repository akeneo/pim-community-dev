import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {DeleteConnection} from '@src/settings/pages/DeleteConnection';
import {renderWithProviders} from '../../../test-utils';

describe('testing DeleteConnection page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('deletes a connection', async () => {
        fetchMock.mockResponseOnce('', {status: 204});

        const history = createMemoryHistory({initialEntries: ['/connections/franklin/delete']});
        const {getByText} = renderWithProviders(
            <Router history={history}>
                <Route path='/connections/:code/delete'>
                    <ConnectionsProvider>
                        <DeleteConnection />
                    </ConnectionsProvider>
                </Route>
            </Router>
        );

        const deleteButton = getByText('pim_common.delete');

        await act(async () => {
            userEvent.click(deleteButton);

            return Promise.resolve();
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_delete?code=franklin');
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'DELETE',
        });

        expect(history.location.pathname).toBe('/connections');
    });
});
