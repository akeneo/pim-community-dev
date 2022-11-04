import fetchMock from 'jest-fetch-mock';

jest.unmock('./useProductMappingSchemaExist');
jest.unmock('./useTargetsQuery');

import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import {useProductMappingSchemaExist} from './useProductMappingSchemaExist';

test('it returns the product mapping schema existence in a given catalog', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {code: 'name', label: 'name'},
            {code: 'body_html', label: 'Description'},
        ])
    );

    const {result, waitForNextUpdate} = renderHook(
        () => useProductMappingSchemaExist('123e4567-e89b-12d3-a456-426614174000'),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    expect(result.current).toBeFalsy();

    await waitForNextUpdate();

    expect(result.current).toBeTruthy();
});

test('it returns false if the product mapping schema does not exist in a given catalog', async () => {
    fetchMock.mockResponseOnce('', {
        status: 204,
    });

    const {result, waitForNextUpdate} = renderHook(
        () => useProductMappingSchemaExist('123e4567-e89b-12d3-a456-426614174000'),
        {
            wrapper: ReactQueryWrapper,
        }
    );

    expect(result.current).toBeFalsy();

    await waitForNextUpdate();

    expect(result.current).toBeFalsy();
});
