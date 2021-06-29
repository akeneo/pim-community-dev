import {DashboardProvider} from '@src/audit/dashboard-context';
import '@testing-library/jest-dom/extend-expect';
import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {UserContext} from '@src/shared/user/user-context';
import {renderWithProviders} from '../../../test-utils';
import {DataDestinationCharts} from '@src/audit/components/DataDestinationCharts';
import {State} from '@src/audit/reducers/dashboard-reducer';
import {FlowType} from '@src/model/flow-type.enum';
import {AuditEventType} from '@src/model/audit-event-type.enum';

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
        [AuditEventType.PRODUCT_READ]: {
            magento: {
                daily: {
                    '2020-01-23': 0,
                    '2020-01-24': 0,
                    '2020-01-25': 0,
                    '2020-01-26': 1024,
                    '2020-01-27': 6,
                    '2020-01-28': 0,
                    '2020-01-29': 0,
                    '2020-01-30': 0,
                },
                weekly_total: 1030,
            },
            '\u003Call\u003E': {
                daily: {
                    '2020-01-23': 0,
                    '2020-01-24': 0,
                    '2020-01-25': 0,
                    '2020-01-26': 1232,
                    '2020-01-27': 2,
                    '2020-01-28': 0,
                    '2020-01-29': 0,
                    '2020-01-30': 0,
                },
                weekly_total: 1234,
            },
        },
    },
};

describe('testing destination chart from Dashboard page', () => {
    it('displays audit data', async () => {
        const api = (route: string) => {
            switch (route) {
                case 'akeneo_connectivity_connection_rest_list':
                    return [
                        {
                            code: 'bynder',
                            label: 'Bynder',
                            flowType: 'data_source',
                            image: null,
                            auditable: false,
                        },
                        {
                            code: 'erp',
                            label: 'ERP',
                            flowType: 'data_source',
                            image: null,
                            auditable: true,
                        },
                        {
                            code: 'magento',
                            label: 'Magento',
                            flowType: 'data_destination',
                            image: null,
                            auditable: true,
                        },
                    ];
                case 'akeneo_connectivity_connection_audit_rest_weekly?event_type=product_read':
                    return {
                        magento: {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 1024,
                                '2020-01-27': 6,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 1030,
                        },
                        '\u003Call\u003E': {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 1232,
                                '2020-01-27': 2,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 1234,
                        },
                    };
            }
            return '';
        };

        jest.spyOn(global, 'fetch').mockImplementation(input =>
            Promise.resolve(new Response(JSON.stringify(api(input as string))))
        );

        const userContext = {
            // eslint-disable-next-line
            get: <T,>(key: string) => {
                let value = key;
                value = 'uiLocale' === key ? 'en_US' : value;
                value = 'timezone' === key ? 'UTC' : value;

                return value as unknown as T;
            },
            set: () => undefined,
            refresh: () => Promise.resolve(),
        };
        const {findByText, queryByText, getByText} = renderWithProviders(
            <UserContext.Provider value={userContext}>
                <DashboardProvider initialState={initialState}>
                    <DataDestinationCharts />
                </DashboardProvider>
            </UserContext.Provider>
        );

        const selector = await findByText('akeneo_connectivity.connection.dashboard.connection_selector.all');

        expect(getByText('1,232')).toBeInTheDocument();
        expect(getByText('1,234')).toBeInTheDocument();
        expect(queryByText('1,024')).toBeNull();
        expect(queryByText('1,030')).toBeNull();

        act(() => {
            userEvent.click(selector);
        });
        act(() => {
            userEvent.click(getByText('Magento'));
        });

        expect(getByText('1,024')).toBeInTheDocument();
        expect(getByText('1,030')).toBeInTheDocument();
        expect(queryByText('1,232')).toBeNull();
        expect(queryByText('1,234')).toBeNull();
    });
});
