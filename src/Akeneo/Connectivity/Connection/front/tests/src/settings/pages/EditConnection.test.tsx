import {act, waitForElement} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Route, Router} from 'react-router-dom';
import {ConnectionsProvider} from '@src/settings/connections-context';
import {EditConnection} from '@src/settings/pages/EditConnection';
import {renderWithProviders} from '../../../test-utils';

jest.mock('@src/common/components/Select2');

describe('testing EditConnection page', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('creates a connection', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify({
                code: 'ecommerce',
                label: 'Franklin',
                flow_type: 'data_source',
                image: null,
                client_id: '<client_id>',
                secret: '<secret>',
                username: 'franklin_<tag>',
                password: null,
                user_role_id: '1',
                user_group_id: '3',
            })
        );
        fetchMock.mockResponseOnce(
            JSON.stringify([
                {id: 1, role: 'ROLE_USER', label: 'User'},
                {id: 2, role: 'ROLE_API', label: 'API Role'},
            ])
        );
        fetchMock.mockResponseOnce(
            JSON.stringify([
                {name: 'All', meta: {id: 3, default: false}},
                {name: 'API Group', meta: {id: 4, default: false}},
            ])
        );
        fetchMock.mockResponseOnce(JSON.stringify({}));

        const history = createMemoryHistory({initialEntries: ['/connections/ecommerce/edit']});
        const {getByText, getByLabelText} = renderWithProviders(
            <Router history={history}>
                <Route path='/connections/:code/edit'>
                    <ConnectionsProvider>
                        <EditConnection />
                    </ConnectionsProvider>
                </Route>
            </Router>
        );

        expect(fetchMock).toBeCalledTimes(1);
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_get?code=ecommerce');

        await waitForElement(() => getByText('Franklin'));

        expect(fetchMock).toBeCalledTimes(3);
        expect(fetchMock.mock.calls[1][0]).toEqual('pim_user_user_role_rest_index');
        expect(fetchMock.mock.calls[2][0]).toEqual('pim_user_user_group_rest_index');

        const labelInput = getByLabelText(/^akeneo_connectivity\.connection\.connection\.label/) as HTMLInputElement;
        const flowTypeSelect = getByText('akeneo_connectivity.connection.flow_type.data_source')
            .parentElement as HTMLSelectElement;
        const userRoleSelect = getByText('User').parentElement as HTMLSelectElement;
        const userGroupSelect = getByText('All').parentElement as HTMLSelectElement;
        const saveButton = getByText('pim_common.save') as HTMLButtonElement;

        await act(async () => {
            await userEvent.type(labelInput, 'Magento');
            userEvent.selectOptions(flowTypeSelect, 'data_destination');
            userEvent.selectOptions(userRoleSelect, '2');
            userEvent.selectOptions(userGroupSelect, '4');
            userEvent.click(saveButton);
        });

        expect(fetchMock).toBeCalledTimes(4);
        expect(fetchMock.mock.calls[3][0]).toEqual('akeneo_connectivity_connection_rest_update?code=ecommerce');
        expect(fetchMock.mock.calls[3][1]).toMatchObject({
            method: 'POST',
            body: JSON.stringify({
                code: 'ecommerce',
                label: 'Magento',
                flow_type: 'data_destination',
                image: null,
                user_role_id: '2',
                user_group_id: '4',
            }),
        });
    });
});
