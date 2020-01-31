import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {RegenerateConnectionSecret} from '@src/settings/pages/RegenerateConnectionSecret';
import {renderWithProviders} from '../../../test-utils';

describe('testing RegenerateConnectionSecret page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('regenerates a connection secret', async () => {
        fetchMock.mockResponseOnce('{}');

        const history = createMemoryHistory({initialEntries: ['/connections/franklin/regenerate-secret']});
        const {getByText} = renderWithProviders(
            <Router history={history}>
                <Route path='/connections/:code/regenerate-secret'>
                    <RegenerateConnectionSecret />
                </Route>
            </Router>
        );

        const regenerateButton = getByText('akeneo_connectivity.connection.regenerate_secret.action.regenerate');

        await act(async () => {
            userEvent.click(regenerateButton);

            return Promise.resolve();
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual(
            'akeneo_connectivity_connection_rest_regenerate_secret?code=franklin'
        );
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'POST',
        });

        expect(history.location.pathname).toBe('/connections/franklin/edit');
    });
});
