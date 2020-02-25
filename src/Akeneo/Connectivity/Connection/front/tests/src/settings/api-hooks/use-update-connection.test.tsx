import {FlowType} from '@src/model/flow-type.enum';
import {useUpdateConnection} from '@src/settings/api-hooks/use-update-connection';
import {err, ok} from '@src/shared/fetch-result/result';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {act, renderHook} from '@testing-library/react-hooks';
import React, {PropsWithChildren} from 'react';

const notify = jest.fn();
const wrapper = ({children}: PropsWithChildren<{}>) => (
    <NotifyContext.Provider value={notify}>{children}</NotifyContext.Provider>
);

describe('useUpdateConnection', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
        notify.mockClear();
    });

    it('updates a connection', async () => {
        fetchMock.mockResponseOnce(JSON.stringify('ok'));

        const {result} = renderHook(() => useUpdateConnection('franklin'), {wrapper});

        let updateConnectionResult;
        await act(async () => {
            updateConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                userRoleId: '1',
                userGroupId: '2',
            });
        });

        expect(fetchMock).toBeCalled();
        expect(fetchMock.mock.calls[0][0]).toEqual('akeneo_connectivity_connection_rest_update?code=franklin');
        expect(fetchMock.mock.calls[0][1]).toMatchObject({
            method: 'POST',
            body: JSON.stringify({
                code: 'franklin',
                label: 'Franklin',
                flow_type: 'data_source',
                image: null,
                auditable: false,
                user_role_id: '1',
                user_group_id: '2',
            }),
        });
        expect(updateConnectionResult).toStrictEqual(ok('ok'));
        expect(notify).toBeCalledWith(
            NotificationLevel.SUCCESS,
            'akeneo_connectivity.connection.edit_connection.flash.success'
        );
    });

    it('handles a bad request', async () => {
        const response = {errors: [{reason: 'reason 1'}, {reason: 'reason 2'}]};
        fetchMock.mockResponseOnce(JSON.stringify(response), {status: 400});

        const {result} = renderHook(() => useUpdateConnection('franklin'), {wrapper});

        let updateConnectionResult;
        await act(async () => {
            updateConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                userRoleId: '1',
                userGroupId: '2',
            });
        });

        expect(updateConnectionResult).toStrictEqual(err(response));
        expect(notify).toBeCalledWith(NotificationLevel.ERROR, 'reason 1');
        expect(notify).toBeCalledWith(NotificationLevel.ERROR, 'reason 2');
    });

    it('handles a unknown error', async () => {
        fetchMock.mockResponseOnce(JSON.stringify('fail'), {status: 400});

        const {result} = renderHook(() => useUpdateConnection('franklin'), {wrapper});

        let updateConnectionResult;
        await act(async () => {
            updateConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
                auditable: false,
                userRoleId: '1',
                userGroupId: '2',
            });
        });

        expect(updateConnectionResult).toStrictEqual(err('fail'));
        expect(notify).toBeCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.edit_connection.flash.error'
        );
    });
});
