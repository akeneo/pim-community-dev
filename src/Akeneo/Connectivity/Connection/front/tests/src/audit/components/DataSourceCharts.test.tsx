import {DashboardProvider} from '@src/audit/dashboard-context';
import '@testing-library/jest-dom/extend-expect';
import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {UserContext} from '@src/shared/user/user-context';
import {renderWithProviders} from '../../../test-utils';
import {DataSourceCharts} from '@src/audit/components/DataSourceCharts';
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
        [AuditEventType.PRODUCT_CREATED]: {
            erp: {
                daily: {
                    '2020-01-23': 0,
                    '2020-01-24': 0,
                    '2020-01-25': 0,
                    '2020-01-26': 2048,
                    '2020-01-27': 1,
                    '2020-01-28': 0,
                    '2020-01-29': 0,
                    '2020-01-30': 0,
                },
                weekly_total: 2049,
            },
            '\u003Call\u003E': {
                daily: {
                    '2020-01-23': 0,
                    '2020-01-24': 0,
                    '2020-01-25': 0,
                    '2020-01-26': 4096,
                    '2020-01-27': 2,
                    '2020-01-28': 0,
                    '2020-01-29': 0,
                    '2020-01-30': 0,
                },
                weekly_total: 4098,
            },
        },
        [AuditEventType.PRODUCT_UPDATED]: {
            erp: {
                daily: {
                    '2020-01-23': 0,
                    '2020-01-24': 0,
                    '2020-01-25': 0,
                    '2020-01-26': 9876,
                    '2020-01-27': 3,
                    '2020-01-28': 0,
                    '2020-01-29': 0,
                    '2020-01-30': 0,
                },
                weekly_total: 515,
            },
            '\u003Call\u003E': {
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
        },
        [AuditEventType.PRODUCT_READ]: {},
    },
};

describe('testing source chart from Dashboard page', () => {
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
                case 'akeneo_connectivity_connection_audit_rest_weekly?event_type=product_created':
                    return {
                        erp: {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 2048,
                                '2020-01-27': 1,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 2049,
                        },
                        '\u003Call\u003E': {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 4096,
                                '2020-01-27': 2,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 4098,
                        },
                    };
                case 'akeneo_connectivity_connection_audit_rest_weekly?event_type=product_updated':
                    return {
                        erp: {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 9876,
                                '2020-01-27': 3,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 515,
                        },
                        '\u003Call\u003E': {
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
                    <DataSourceCharts />
                </DashboardProvider>
            </UserContext.Provider>
        );

        const selector = await findByText('akeneo_connectivity.connection.dashboard.connection_selector.all');

        expect(getByText('1,024')).toBeInTheDocument();
        expect(getByText('1,030')).toBeInTheDocument();
        expect(getByText('4,096')).toBeInTheDocument();
        expect(getByText('4,098')).toBeInTheDocument();
        expect(queryByText('9876')).toBeNull();
        expect(queryByText('515')).toBeNull();
        expect(queryByText('2,048')).toBeNull();
        expect(queryByText('2,049')).toBeNull();

        act(() => {
            userEvent.click(selector);
        });
        act(() => {
            userEvent.click(getByText('ERP'));
        });

        expect(queryByText('1,024')).toBeNull();
        expect(queryByText('1,030')).toBeNull();
        expect(queryByText('4,096')).toBeNull();
        expect(queryByText('4,098')).toBeNull();

        expect(getByText('2,048')).toBeInTheDocument();
        expect(getByText('2,049')).toBeInTheDocument();
        expect(getByText('515')).toBeInTheDocument();
        expect(getByText('9876')).toBeInTheDocument();
    });
});
