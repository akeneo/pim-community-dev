import {DashboardProvider} from '@src/audit/dashboard-context';
import useConnectionSelect from '@src/audit/hooks/useConnectionSelect';
import {State} from '@src/audit/reducers/dashboard-reducer';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {FlowType} from '@src/model/flow-type.enum';
import {act, renderHook} from '@testing-library/react-hooks';
import React, {PropsWithChildren} from 'react';

const initialState: State = {
    connections: {
        bynder: {
            code: 'bynder',
            label: 'Bynder',
            flowType: FlowType.DATA_SOURCE,
            image: null,
            auditable: true,
        },
        magento: {
            code: 'magento',
            label: 'Magento',
            flowType: FlowType.DATA_DESTINATION,
            image: null,
            auditable: true,
        },
    },
    events: {
        [AuditEventType.PRODUCT_CREATED]: {},
        [AuditEventType.PRODUCT_UPDATED]: {},
        [AuditEventType.PRODUCT_READ]: {},
    },
};

const wrapper = ({children}: PropsWithChildren<{}>) => (
    <DashboardProvider initialState={initialState}>{children}</DashboardProvider>
);

describe('Select connection', () => {
    it('selects a connection', () => {
        const {result} = renderHook(() => useConnectionSelect(FlowType.DATA_DESTINATION), {wrapper});
        const {connections, connectionCode, selectConnectionCode} = result.current;

        expect(connections).toStrictEqual([
            {
                code: '<all>',
                label: 'akeneo_connectivity.connection.dashboard.connection_selector.all',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
                auditable: true,
            },
            {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
                auditable: true,
            },
        ]);

        expect(connectionCode).toBe('<all>');

        act(() => {
            selectConnectionCode('magento');
        });

        const {connectionCode: selectedConnectionCode} = result.current;
        expect(selectedConnectionCode).toBe('magento');
    });
});
