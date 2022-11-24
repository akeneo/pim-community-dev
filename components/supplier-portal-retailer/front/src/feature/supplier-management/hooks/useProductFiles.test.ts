import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useProductFiles} from './useProductFiles';

const backendResponse = {
    product_files: [
        {
            identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
            path: '/path/to/file.xlsx',
            uploadedAt: '2022-07-22 16:50:45',
            uploadedByContributor: 'a@a.a',
            hasUnreadComments: false,
            importStatus: 'in_progress',
        },
        {
            identifier: 'bbe78bfb-10e8-4cd8-ad9c-22056824e9bd',
            path: '/path/to/file2.xlsx',
            uploadedAt: '2022-06-15 10:08:11',
            uploadedByContributor: 'a@a.a',
            hasUnreadComments: true,
            importStatus: 'in_progress',
        },
    ],
    total: 2,
    items_per_page: 25,
};

test('it loads the product files', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));

    const {result, waitForNextUpdate} = renderHookWithProviders(() =>
        useProductFiles('bbe78bfb-10e8-4cd8-ad9c-22056824e9bd', 1)
    );

    expect(result.current[0]).toEqual([]);

    await waitForNextUpdate();

    expect(result.current[0]).toStrictEqual([
        {
            identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
            uploadedAt: '2022-07-22 16:50:45',
            contributor: 'a@a.a',
            hasUnreadComments: false,
            importStatus: 'in_progress',
        },
        {
            identifier: 'bbe78bfb-10e8-4cd8-ad9c-22056824e9bd',
            uploadedAt: '2022-06-15 10:08:11',
            contributor: 'a@a.a',
            hasUnreadComments: true,
            importStatus: 'in_progress',
        },
    ]);
    expect(result.current[1]).toBe(backendResponse.total);
});

test('it renders an error notification if the loading of the suppliers failed', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await renderHookWithProviders(() => useProductFiles('bbe78bfb-10e8-4cd8-ad9c-22056824e9bd', 1));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.title',
        'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.content'
    );
});
