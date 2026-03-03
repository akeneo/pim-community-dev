import React, {PropsWithChildren} from 'react';
import {FlowType} from '@src/model/flow-type.enum';
import {ok, err} from '@src/shared/fetch-result/result';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {act, renderHook} from '@testing-library/react-hooks';
import {useCreateConnection} from '@src/settings/api-hooks/use-create-connection';

const notify = jest.fn();
const wrapper = ({children}: PropsWithChildren<{}>) => (
    <NotifyContext.Provider value={notify}>{children}</NotifyContext.Provider>
);

describe('useCreateConnection', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
        notify.mockClear();
    });

    it('creates a connection', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify({
                code: 'franklin',
                label: 'Franklin',
                flow_type: 'data_source',
                image: null,
                user_role_id: '1',
                user_group_id: '2',
                client_id: '<client_id>',
                secret: '<secret>',
                username: 'franklin_1234',
                password: '<password>',
            })
        );

        const {result} = renderHook(() => useCreateConnection(), {wrapper});

        let createConnectionResult;
        await act(async () => {
            createConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flow_type: FlowType.DATA_SOURCE,
            });
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_create');
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'POST',
            body: JSON.stringify({
                code: 'franklin',
                label: 'Franklin',
                flow_type: 'data_source',
            }),
        });
        expect(createConnectionResult).toMatchObject(
            ok({
                code: 'franklin',
                label: 'Franklin',
                flowType: 'data_source',
                image: null,
                userRoleId: '1',
                userGroupId: '2',
                clientId: '<client_id>',
                secret: '<secret>',
                username: 'franklin_1234',
                password: '<password>',
            })
        );
        expect(notify).toBeCalledWith(
            NotificationLevel.SUCCESS,
            'akeneo_connectivity.connection.create_connection.flash.success'
        );
    });

    it('handles errors', async () => {
        fetchMock.mockResponseOnce(JSON.stringify('fail'), {status: 400});

        const {result} = renderHook(() => useCreateConnection(), {wrapper});

        let createConnectionResult;
        await act(async () => {
            createConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flow_type: FlowType.DATA_SOURCE,
            });
        });

        expect(createConnectionResult).toStrictEqual(err('fail'));
        expect(notify).toBeCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.create_connection.flash.error'
        );
    });
});
