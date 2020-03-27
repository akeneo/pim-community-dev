import {DashboardProvider} from '@src/audit/dashboard-context';
import {Dashboard} from '@src/audit/pages/Dashboard';
import '@testing-library/jest-dom/extend-expect';
import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {UserContext} from '@src/shared/user/user-context';
import {renderWithProviders} from '../../../test-utils';

/* TODO */
describe('testing Dashboard page', () => {
    it('displays audit data', async () => {
        return;
        const api = (route: string) => {
            switch (route) {
                case 'akeneo_connectivity_connection_rest_list':
                    return [
                        {
                            code: 'bynder',
                            label: 'Bynder',
                            flowType: 'data_source',
                            image: null,
                            auditable: true,
                        },
                        {
                            code: 'erp',
                            label: 'ERP',
                            flowType: 'data_source',
                            image: null,
                            auditable: false,
                        },
                        {
                            code: 'magento',
                            label: 'Magento',
                            flowType: 'data_destination',
                            image: null,
                        },
                    ];
                case 'akeneo_connectivity_connection_audit_rest_weekly?event_type=product_created':
                    return {
                        bynder: {
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
                        bynder: {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 789,
                                '2020-01-27': 3,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 792,
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
                case 'akeneo_connectivity_connection_audit_rest_weekly?event_type=product_read':
                    return {
                        magento: {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 32768,
                                '2020-01-27': 7,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 32775,
                        },
                        '\u003Call\u003E': {
                            daily: {
                                '2020-01-23': 0,
                                '2020-01-24': 0,
                                '2020-01-25': 0,
                                '2020-01-26': 65536,
                                '2020-01-27': 8,
                                '2020-01-28': 0,
                                '2020-01-29': 0,
                                '2020-01-30': 0,
                            },
                            weekly_total: 65544,
                        },
                    };
            }
            return '';
        };

        jest.spyOn(global, 'fetch').mockImplementation(input =>
            Promise.resolve(new Response(JSON.stringify(api(input as string))))
        );

        const userContext = {
            get: (key: string) => {
                if ('uiLocale' === key) {
                    return 'en_US';
                }
                if ('timezone' === key) {
                    return 'UTC';
                }

                return key;
            },
            set: () => undefined,
        };
        const {findAllByText, queryByText, getByText} = renderWithProviders(
            <UserContext.Provider value={userContext}>
                <DashboardProvider>
                    <Dashboard />
                </DashboardProvider>
            </UserContext.Provider>
        );

        const selectors = await findAllByText('akeneo_connectivity.connection.dashboard.connection_selector.all');

        act(() => {
            userEvent.click(selectors[0]);
            userEvent.click(selectors[1]);
        });

        getByText('1024');
        getByText('4096');
        getByText('65536');
        expect(queryByText('512')).toBeNull();
        expect(queryByText('2048')).toBeNull();
        expect(queryByText('32768')).toBeNull();

        act(() => {
            userEvent.click(selectors[0]);
            userEvent.click(getByText('Bynder'));
        });

        getByText('789');
        expect(getByText('2048')).toBeInTheDocument();
        expect(getByText('65536')).toBeInTheDocument();
        expect(queryByText('1024')).toBeNull();
        expect(queryByText('4096')).toBeNull();
        expect(queryByText('32768')).toBeNull();

        act(() => {
            userEvent.click(selectors[1]);
            userEvent.click(getByText('Magento'));
        });

        expect(queryByText('512')).toBeInTheDocument();
        expect(queryByText('2048')).toBeInTheDocument();
        expect(queryByText('32768')).toBeInTheDocument();
        expect(queryByText('1024')).toBeNull();
        expect(queryByText('4096')).toBeNull();
        expect(queryByText('65536')).toBeNull();
    });
});
