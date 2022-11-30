import {renderHookWithProviders} from '../../../tests';
import {useFilteredProductFiles} from './useFilteredProductFiles';
import {ImportStatus} from '../model/ImportStatus';
import {ProductFile} from '../model/ProductFile';

const productFiles: ProductFile[] = [
    {
        identifier: '2c23beb2-be85-4b9f-bec9-7ba51b9dd267',
        filename: 'shoes.xlsx',
        contributor: 'jimmy@supplier.com',
        uploadedAt: '2022-11-30 11:01:00',
        comments: [],
        supplierLastReadAt: null,
        displayNewMessageIndicatorPill: false,
        importStatus: ImportStatus.TO_IMPORT,
    },
    {
        identifier: '352db03d-2950-4edc-b4d4-3f7feedd7188',
        filename: 't-shirts.xlsx',
        contributor: 'jimmy@supplier.com',
        uploadedAt: '2022-11-30 11:01:10',
        comments: [],
        supplierLastReadAt: null,
        displayNewMessageIndicatorPill: false,
        importStatus: ImportStatus.TO_IMPORT,
    },
];

test('it returns all product files by default', () => {
    const {result} = renderHookWithProviders(() => useFilteredProductFiles(productFiles, ''));
    expect(result.current).toStrictEqual(productFiles);
});

test('it filters product files by name', async () => {
    const {result} = renderHookWithProviders(() => useFilteredProductFiles(productFiles, 'shoe'));
    expect(result.current).toStrictEqual([
        {
            identifier: '2c23beb2-be85-4b9f-bec9-7ba51b9dd267',
            filename: 'shoes.xlsx',
            contributor: 'jimmy@supplier.com',
            uploadedAt: '2022-11-30 11:01:00',
            comments: [],
            supplierLastReadAt: null,
            displayNewMessageIndicatorPill: false,
            importStatus: ImportStatus.TO_IMPORT,
        },
    ]);
});

test('it returns no result if the search does not match any name', async () => {
    const {result} = renderHookWithProviders(() => useFilteredProductFiles(productFiles, 'unknown'));
    expect(result.current).toStrictEqual([]);
});
