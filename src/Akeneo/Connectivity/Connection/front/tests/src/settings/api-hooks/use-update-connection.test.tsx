import React, {PropsWithChildren} from 'react';
import {FlowType} from '@src/model/flow-type.enum';
import {useUpdateConnection} from '@src/settings/api-hooks/use-update-connection';
import {ok} from '@src/shared/fetch-result/result';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {act, renderHook} from '@testing-library/react-hooks';

describe('useUpdateConnection', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('updates a connection', async () => {
        fetchMock.mockResponseOnce(JSON.stringify('ok'));

        const notify = jest.fn();
        const wrapper = ({children}: PropsWithChildren<{}>) => (
            <NotifyContext.Provider value={notify}>{children}</NotifyContext.Provider>
        );

        const {result} = renderHook(() => useUpdateConnection('franklin'), {
            wrapper,
        });

        let updateConnectionResult;
        await act(async () => {
            updateConnectionResult = await result.current({
                code: 'franklin',
                label: 'Franklin',
                flowType: FlowType.DATA_SOURCE,
                image: null,
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
});
