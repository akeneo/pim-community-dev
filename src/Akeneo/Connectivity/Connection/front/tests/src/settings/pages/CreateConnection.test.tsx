import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {CreateConnection} from '@src/settings/pages/CreateConnection';
import {renderWithProviders} from '../../../test-utils';

jest.mock('@src/common/components/Select2');

describe('testing CreateConnection page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('creates a connection', async () => {
        fetchMock.mockResponseOnce('{}', {status: 201});

        const history = createMemoryHistory({initialEntries: ['/connections/create']});
        const {getByText, getByLabelText} = renderWithProviders(
            <Router history={history}>
                <Route path='/connections/create'>
                    <ConnectionsProvider>
                        <CreateConnection />
                    </ConnectionsProvider>
                </Route>
            </Router>
        );

        const labelInput = getByLabelText(/^akeneo_connectivity\.connection\.connection\.label/) as HTMLInputElement;
        const codeInput = getByLabelText(/^akeneo_connectivity\.connection\.connection\.code/) as HTMLInputElement;
        const flowTypeSelect = getByText('akeneo_connectivity.connection.flow_type.data_source')
            .parentElement as HTMLSelectElement;
        const saveButton = getByText('pim_common.save') as HTMLButtonElement;

        await act(async () => {
            await userEvent.type(labelInput, 'Magento');
            await userEvent.type(codeInput, 'magento');
            userEvent.selectOptions(flowTypeSelect, 'data_destination');
            userEvent.click(saveButton);
        });

        expect(labelInput.value).toBe('Magento');
        expect(codeInput.value).toBe('magento');
        expect(flowTypeSelect.value).toBe('data_destination');

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

        expect(history.location.pathname).toBe('/connections/magento/edit');
    });
});
