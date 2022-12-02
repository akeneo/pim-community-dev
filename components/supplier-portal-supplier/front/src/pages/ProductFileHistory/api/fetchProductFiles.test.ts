import {apiFetch} from '../../../api/apiFetch';
import {fetchProductFiles} from './fetchProductFiles';
import {ImportStatus} from '../model/ImportStatus';

jest.mock('../../../api/apiFetch');

test('it calls the backend and returns the list of product files', async () => {
    apiFetch.mockResolvedValue({
        product_files: [
            {
                identifier: '2b0f733e-6038-40a3-bca3-52a4803def89',
                originalFilename: 'suppliers_export.xlsx',
                path: 'test/33102208-5edb-46dd-a4da-005f0cedce2d-suppliers_export.xlsx',
                uploadedByContributor: 'a@a.a',
                uploadedBySupplier: '7afca148-ee9c-4e00-8f28-9ce13c745600',
                uploadedAt: '2022-10-20T14:19:34+00:00',
                importStatus: ImportStatus.TO_IMPORT,
                retailerComments: [],
                supplierComments: [
                    {
                        content: 'test',
                        created_at: '2022-10-22T14:41:02+00:00',
                        author_email: 'a@a.a',
                    },
                    {
                        content: 'test',
                        created_at: '2022-10-10T14:41:18+00:00',
                        author_email: 'a@a.a',
                    },
                ],
                retailerLastReadAt: null,
                supplierLastReadAt: '2022-10-18T09:51:24+00:00',
            },
        ],
        total_number_of_product_files: 1,
        total_search_results: 1,
    });

    const response = await fetchProductFiles(1, '');
    expect(response).toEqual({
        product_files: [
            {
                identifier: '2b0f733e-6038-40a3-bca3-52a4803def89',
                filename: 'suppliers_export.xlsx',
                contributor: 'a@a.a',
                uploadedAt: '2022-10-20T14:19:34+00:00',
                importStatus: ImportStatus.TO_IMPORT,
                comments: [
                    {
                        authorEmail: 'a@a.a',
                        content: 'test',
                        createdAt: '2022-10-10T14:41:18+00:00',
                        outgoing: true,
                    },
                    {
                        authorEmail: 'a@a.a',
                        content: 'test',
                        createdAt: '2022-10-22T14:41:02+00:00',
                        outgoing: true,
                    },
                ],
                supplierLastReadAt: '2022-10-18T09:51:24+00:00',
                displayNewMessageIndicatorPill: true,
            },
        ],
        totalNumberOfProductFiles: 1,
        totalSearchResults: 1,
    });
});
