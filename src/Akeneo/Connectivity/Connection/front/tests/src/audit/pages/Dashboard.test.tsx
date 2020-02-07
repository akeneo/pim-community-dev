import {DashboardProvider} from '@src/audit/dashboard-context';
import {Dashboard} from '@src/audit/pages/Dashboard';
import '@testing-library/jest-dom/extend-expect';
import {act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {renderWithProviders} from '../../../test-utils';

describe('testing Dashboard page', () => {
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
                        },
                    ];
                case 'akeneo_connectivity_connection_rest_audit_source_connections_event?event_type=product_created':
                    return {
                        bynder: {
                            '2020-01-23': 0,
                            '2020-01-24': 0,
                            '2020-01-25': 0,
                            '2020-01-26': 4096,
                            '2020-01-27': 0,
                            '2020-01-28': 0,
                            '2020-01-29': 0,
                            '2020-01-30': 0,
                        },
                        '\u003Call\u003E': {
                            '2020-01-23': 0,
                            '2020-01-24': 0,
                            '2020-01-25': 0,
                            '2020-01-26': 2048,
                            '2020-01-27': 0,
                            '2020-01-28': 0,
                            '2020-01-29': 0,
                            '2020-01-30': 0,
                        },
                    };
            }
            return '';
        };

        jest.spyOn(global, 'fetch').mockImplementation(input =>
            Promise.resolve(new Response(JSON.stringify(api(input as string))))
        );

        const {findAllByText, queryByText, getByText} = renderWithProviders(
            <DashboardProvider>
                <Dashboard />
            </DashboardProvider>
        );

        const selectors = await findAllByText('akeneo_connectivity.connection.dashboard.connection_selector.all');
        act(() => {
            userEvent.click(selectors[0]);
        });

        getByText('2048');
        expect(queryByText('4096')).toBeNull();

        act(() => {
            userEvent.click(getByText('Bynder'));
        });

        expect(queryByText('2048')).toBeNull();
        getByText('4096');
    });
});
