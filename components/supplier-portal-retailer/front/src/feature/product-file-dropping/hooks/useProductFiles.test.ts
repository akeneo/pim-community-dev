import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useProductFiles} from './useProductFiles';

const backendResponse = {
    product_files: [
        {
            identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
            path: '/path/to/file.xlsx',
            uploadedAt: '2022-07-22 16:50:45',
            uploadedByContributor: 'a@a.a',
            uploadedBySupplier: 'test',
            hasUnreadComments: true,
            importStatus: 'in_progress',
            importDate: '2022-07-23 16:50:45',
            supplierLabel: 'Los Pollos Hermanos',
            originalFilename: 'file1.xlsx',
        },
        {
            identifier: 'bbe78bfb-10e8-4cd8-ad9c-22056824e9bd',
            path: '/path/to/file2.xlsx',
            uploadedAt: '2022-06-15 10:08:11',
            uploadedByContributor: 'a@a.a',
            uploadedBySupplier: 'test',
            hasUnreadComments: false,
            importStatus: 'in_progress',
            importDate: '2022-06-16 16:50:45',
            supplierLabel: 'Los Pollos Hermanos',
            originalFilename: 'file2.xlsx',
        },
    ],
    total: 2,
    items_per_page: 25,
};

test('it loads the product files', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useProductFiles(1, '', () => {}, 'in_progress'));

    expect(result.current[0]).toEqual([]);

    await waitForNextUpdate();

    expect(result.current[0]).toStrictEqual([
        {
            identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
            uploadedAt: '2022-07-22 16:50:45',
            contributor: 'a@a.a',
            supplier: 'test',
            hasUnreadComments: true,
            importStatus: 'in_progress',
            importedAt: '2022-07-23 16:50:45',
            supplierLabel: 'Los Pollos Hermanos',
            filename: 'file1.xlsx',
        },
        {
            identifier: 'bbe78bfb-10e8-4cd8-ad9c-22056824e9bd',
            uploadedAt: '2022-06-15 10:08:11',
            contributor: 'a@a.a',
            supplier: 'test',
            hasUnreadComments: false,
            importStatus: 'in_progress',
            importedAt: '2022-06-16 16:50:45',
            supplierLabel: 'Los Pollos Hermanos',
            filename: 'file2.xlsx',
        },
    ]);
    expect(result.current[1]).toBe(backendResponse.total);
});

test('it renders an error notification if the loading of the suppliers failed', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await renderHookWithProviders(() => useProductFiles(1, '', () => {}, 'in_progress'));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.title',
        'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.content'
    );
});
