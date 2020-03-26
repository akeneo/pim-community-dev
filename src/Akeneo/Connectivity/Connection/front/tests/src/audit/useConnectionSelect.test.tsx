import React, {PropsWithChildren} from 'react';
import useConnectionSelect from '@src/audit/useConnectionSelect';
import {FlowType} from '@src/model/flow-type.enum';
import {act, renderHook} from '@testing-library/react-hooks';
import {DashboardProvider} from '@src/audit/dashboard-context';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {State} from '@src/audit/reducers/dashboard-reducer';

const initialState: State = {
    connections: {
        bynder: {
            code: 'bynder',
            label: 'Bynder',
            flowType: FlowType.DATA_SOURCE,
            image: null,
        },
        magento: {
            code: 'magento',
            label: 'Magento',
            flowType: FlowType.DATA_DESTINATION,
            image: null,
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
        const [connections, defaultConnectionCode, setSelectedConnectionCode] = result.current;
        expect(connections).toStrictEqual([
            {
                code: '<all>',
                label: 'akeneo_connectivity.connection.dashboard.connection_selector.all',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
            },
            {
                code: 'magento',
                label: 'Magento',
                flowType: FlowType.DATA_DESTINATION,
                image: null,
            },
        ]);
        expect(defaultConnectionCode).toBe('<all>');
        act(() => {
            setSelectedConnectionCode('magento');
        });
        const [, selectedConnectionCode] = result.current;
        expect(selectedConnectionCode).toBe('magento');
    });
});
