import {Index} from '@src/error-management/pages/Index';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Router} from 'react-router-dom';
import {fetchMockResponseOnce, renderWithProviders} from '../../../test-utils';

test('renders the connection monitoring page', async () => {
    fetchMockResponseOnce(
        'akeneo_connectivity_connection_rest_get?code=erp',
        JSON.stringify({label: 'ERP', flow_type: 'data_source', auditable: true})
    );

    fetchMockResponseOnce(
        'akeneo_connectivity_connection_error_management_rest_get_connection_business_errors?connection_code=erp',
        JSON.stringify([
            {
                date_time: '2020-01-01T00:12:00+00:00',
                content: {
                    message: 'First error message',
                    type: 'domain_error',
                },
            },
            {
                date_time: '2020-01-02T00:00:00+00:00',
                content: {
                    message: 'Second error message',
                    type: 'domain_error',
                },
            },
        ])
    );

    fetchMockResponseOnce('pim_enrich_channel_rest_index', JSON.stringify([]));
    fetchMockResponseOnce('pim_enrich_locale_rest_index', JSON.stringify([]));
    fetchMockResponseOnce('pim_enrich_family_rest_index', JSON.stringify({}));

    const history = createMemoryHistory({initialEntries: ['/connect/connection-settings/erp/monitoring']});

    const {findByText} = renderWithProviders(
        <Router history={history}>
            <Index />
        </Router>
    );

    await findByText('ERP');

    await findByText('01/01/2020');
    await findByText('12:12:00 AM');
    await findByText('First error message');

    await findByText('01/02/2020');
    await findByText('12:00:00 AM');
    await findByText('Second error message');
});

test('renders the connection monitoring page with no error', async () => {
    fetchMockResponseOnce(
        'akeneo_connectivity_connection_rest_get?code=erp',
        JSON.stringify({label: 'ERP', flow_type: 'data_source', auditable: true})
    );

    fetchMockResponseOnce('pim_enrich_channel_rest_index', JSON.stringify([]));
    fetchMockResponseOnce('pim_enrich_locale_rest_index', JSON.stringify([]));
    fetchMockResponseOnce('pim_enrich_family_rest_index', JSON.stringify({}));

    fetchMockResponseOnce(
        'akeneo_connectivity_connection_error_management_rest_get_connection_business_errors?connection_code=erp',
        JSON.stringify([])
    );

    const history = createMemoryHistory({initialEntries: ['/connect/connection-settings/erp/monitoring']});

    const {findByText} = renderWithProviders(
        <Router history={history}>
            <Index />
        </Router>
    );

    await findByText('ERP');

    await findByText('akeneo_connectivity.connection.error_management.connection_monitoring.no_error.title');
});
