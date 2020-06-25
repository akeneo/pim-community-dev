import {ErrorList} from '@src/error-management/components/ErrorList';
import {fireEvent, waitForDomChange} from '@testing-library/dom';
import React from 'react';
import {renderWithProviders} from '../../../test-utils';
import {ConnectionError} from '@src/error-management/model/ConnectionError';

test('filters errors by search value', async () => {
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

    getByText('"Error 1"');
    getByText('"Error 2"');

    fireEvent.change(
        getByPlaceholderText(
            'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.placeholder'
        ),
        {target: {value: '"Error 2"'}}
    );

    await waitForDomChange();

    expect(queryByText('"Error 1"')).toBeNull();
    getByText('"Error 2"');
});
