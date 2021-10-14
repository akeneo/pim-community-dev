import {ConnectionsProvider} from '@src/settings/connections-context';
import {CreateConnection} from '@src/settings/pages/CreateConnection';
import {act, screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {renderWithProviders} from '../../../test-utils';

describe('testing CreateConnection page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('creates a connection', async () => {
        fetchMock.mockResponseOnce('{}', {status: 201});

        const history = createMemoryHistory({initialEntries: ['/connections/create']});
        renderWithProviders(
            <Router history={history}>
                <Route path='/connections/create'>
                    <ConnectionsProvider>
                        <CreateConnection />
                    </ConnectionsProvider>
                </Route>
            </Router>
        );

        const labelInput = screen.getByLabelText(
            /^akeneo_connectivity\.connection\.connection\.label/
        ) as HTMLInputElement;
        const codeInput = screen.getByLabelText(
            /^akeneo_connectivity\.connection\.connection\.code/
        ) as HTMLInputElement;
        const flowTypeSelect = screen.getByLabelText(
            /^akeneo_connectivity\.connection\.connection\.flow_type/
        ) as HTMLSelectElement;
        const saveButton = screen.getByText('pim_common.save') as HTMLButtonElement;

        await act(async () => {
            userEvent.clear(labelInput);
            await waitFor(() => expect(labelInput.value).toBe(''));
            userEvent.type(labelInput, 'Magento');

            userEvent.click(flowTypeSelect);
            userEvent.click(await screen.findByText(/akeneo_connectivity\.connection\.flow_type\.data_destination/));
            userEvent.click(saveButton);
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_create');
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'POST',
            body: JSON.stringify({
                code: 'magento',
                label: 'Magento',
                flow_type: 'data_destination',
            }),
        });

        expect(history.location.pathname).toBe('/connect/connection-settings/magento/edit');
    });
});
