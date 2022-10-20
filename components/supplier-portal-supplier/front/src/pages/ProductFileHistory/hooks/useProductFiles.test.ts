import {renderHookWithProviders} from '../../../tests';
import {useProductFiles} from './useProductFiles';
import {act} from '@testing-library/react-hooks';

const productFiles = [
    {
        identifier: '4b5ca8e4-0f89-4de0-9bc7-20c7617a9c86',
        filename: 'suppliers_export.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-28 14:57:38',
    },
    {
        identifier: '8be6446b-befb-4d9f-aa94-0dfd390df690',
        filename: 'suppliers_export-2.xlsx',
        path: 'test/73d1078b-840c-4135-9564-682f8cbfb982-suppliers_export.xlsx',
        contributor: 'contributor@example.com',
        uploadedAt: '2022-07-28 14:57:38',
    },
];

jest.mock('../api/fetchProductFiles', () => ({
    fetchProductFiles: () => {
        return new Promise(resolve => resolve(productFiles));
    },
}));

test('it fetches the product files', async () => {
    const {result, waitForNextUpdate} = renderHookWithProviders(() => useProductFiles(1));

    await act(async () => {
        await waitForNextUpdate();
    });

    expect(result.current).toStrictEqual(productFiles);
});
