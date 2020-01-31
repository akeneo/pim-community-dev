import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {RegenerateConnectionPassword} from '@src/settings/pages/RegenerateConnectionPassword';
import {renderWithProviders} from '../../../test-utils';

describe('testing RegenerateConnectionPassword page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('regenerates a connection password', async () => {
        fetchMock.mockResponseOnce('{}');

        const history = createMemoryHistory({initialEntries: ['/connections/franklin/regenerate-password']});
        const {getByText} = renderWithProviders(
            <Router history={history}>
                <Route path='/connections/:code/regenerate-password'>
                    <ConnectionsProvider>
                        <RegenerateConnectionPassword />
                    </ConnectionsProvider>
                </Route>
            </Router>
        );

        const regenerateButton = getByText('akeneo_connectivity.connection.regenerate_password.action.regenerate');

        await act(async () => {
            userEvent.click(regenerateButton);

            return Promise.resolve();
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual(
            'akeneo_connectivity_connection_rest_regenerate_password?code=franklin'
        );
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'POST',
        });

        expect(history.location.pathname).toBe('/connections/franklin/edit');
    });
});
