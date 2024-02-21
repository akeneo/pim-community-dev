import {DashboardProvider} from '@src/audit/dashboard-context';
import '@testing-library/jest-dom/extend-expect';
import React from 'react';
import {renderWithProviders} from '../../../../test-utils';
import {State} from '@src/audit/reducers/dashboard-reducer';
import {FlowType} from '@src/model/flow-type.enum';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {BusinessErrorCountWidget} from '@src/audit/components/ErrorManagement/BusinessErrorCountWidget';

const initialState: State = {
    connections: {
        bynder: {
            code: 'bynder',
            label: 'Bynder',
            flowType: FlowType.DATA_SOURCE,
            image: null,
            auditable: false,
        },
        erp: {
            code: 'erp',
            label: 'ERP',
            flowType: FlowType.DATA_SOURCE,
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

describe('testing business error count widget', () => {
    it('displays business error count per connection for the last seven days', async () => {
        const api = (route: string) => {
            switch (route) {
                case 'akeneo_connectivity_connection_audit_rest_error_count_per_connection?error_type=business':
                    return {erp: 36, bynder: 42};
            }
            return '';
        };

        jest.spyOn(global, 'fetch').mockImplementation(input =>
            Promise.resolve(new Response(JSON.stringify(api(input as string))))
        );

        const {findByText} = renderWithProviders(
            <DashboardProvider initialState={initialState}>
                <BusinessErrorCountWidget />
            </DashboardProvider>
        );

        await findByText('ERP');
        await findByText('36');
        await findByText('Bynder');
        await findByText('42');
    });
});
