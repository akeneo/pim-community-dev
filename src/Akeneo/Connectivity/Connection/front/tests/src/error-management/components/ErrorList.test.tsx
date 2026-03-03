import React from 'react';
import {ErrorList} from '@src/error-management/components/ErrorList';
import {ConnectionError} from '@src/error-management/model/ConnectionError';
import {fireEvent} from '@testing-library/dom';
import {fetchMockResponseOnce, renderWithProviders} from '../../../test-utils';
import {waitFor} from '@testing-library/react';

test('filters errors by search value', async () => {
    fetchMockResponseOnce(
        'pim_enrich_channel_rest_index',
        JSON.stringify([
            {code: 'ecommerce', labels: {de_DE: 'Ecommerce', en_US: 'Ecommerce', fr_FR: 'Ecommerce'}},
            {code: 'mobile', labels: {de_DE: 'Mobil', en_US: 'Mobile', fr_FR: 'Mobile'}},
            {code: 'print', labels: {de_DE: 'Drucken', en_US: 'Print', fr_FR: 'Impression'}},
        ])
    );

    fetchMockResponseOnce(
        'pim_enrich_locale_rest_index',
        JSON.stringify([
            {code: 'en_US', language: 'English'},
            {code: 'de_DE', language: 'German'},
            {code: 'fr_FR', language: 'French'},
        ])
    );

    fetchMockResponseOnce(
        'pim_enrich_family_rest_index',
        JSON.stringify({
            accessories: {labels: {en_US: 'Accessories', fr_FR: 'Accessoires', de_DE: 'Zubehör'}},
            loudspeakers: {labels: {en_US: 'Loudspeakers', fr_FR: 'Hauts-parleurs', de_DE: 'Lautsprecher'}},
            shoes: {labels: {en_US: 'Shoes', fr_FR: 'Chaussures', de_DE: 'Schuhes'}},
        })
    );

    const errors: ConnectionError[] = [
        {
            id: 1,
            timestamp: 0,
            content: {
                type: 'violation_error',
                message: 'Error 1',
            },
        },
        {
            id: 2,
            timestamp: 0,
            content: {
                type: 'violation_error',
                message: 'Error 2',
            },
        },
    ];

    const {getByText, getByPlaceholderText, queryByText} = renderWithProviders(<ErrorList errors={errors} />);

    getByText('Error 1');
    getByText('Error 2');

    fireEvent.change(
        getByPlaceholderText(
            'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.placeholder'
        ),
        {target: {value: 'Error 2'}}
    );

    await waitFor(() => {
        expect(queryByText('Error 1')).toBeNull();
    });

    expect(queryByText('Error 1')).toBeNull();
    getByText('Error 2');
});
