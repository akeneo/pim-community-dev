import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useProductFile} from './useProductFile';
import {act} from '@testing-library/react-hooks';
import {waitFor} from '@testing-library/react';

const backendResponse = {
    identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
    originalFilename: 'file.xlsx',
    uploadedAt: '2022-07-22 16:50:45',
    uploadedByContributor: 'jimmy@supplier.com',
    uploadedBySupplier: '3d845092-0356-4895-8863-99a9a6ff172f',
    retailerComments: [],
    supplierComments: [],
};

test('it loads a product file', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));

    const {result, waitForNextUpdate} = renderHookWithProviders(() =>
        useProductFile('cd2c0741-0b27-4484-a927-b5e53c8f715c')
    );

    await act(async () => await waitForNextUpdate());

    expect(result.current[0]).toEqual({
        identifier: 'cd2c0741-0b27-4484-a927-b5e53c8f715c',
        originalFilename: 'file.xlsx',
        uploadedAt: '2022-07-22 16:50:45',
        contributor: 'jimmy@supplier.com',
        supplier: '3d845092-0356-4895-8863-99a9a6ff172f',
        retailerComments: [],
        supplierComments: [],
    });
});

test('it renders an error notification if the loading of the product file failed', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    renderHookWithProviders(() => useProductFile('foo'));

    await waitFor(() => {
        expect(notify).toHaveBeenNthCalledWith(
            1,
            NotificationLevel.ERROR,
            'supplier_portal.product_file_dropping.supplier_files.discussion.loading_error'
        );
    });
});

test('it renders an error notification if the submitted comment is empty', async () => {
    const backendValidationErrors = [
        {
            propertyPath: 'content',
            message: 'The comment should not be empty.',
            invalidValue: '',
        },
    ];

    global.fetch = jest
        .fn()
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => backendResponse,
        }))
        .mockImplementationOnce(async () => ({
            ok: false,
            json: async () => backendValidationErrors,
        }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() =>
        useProductFile('cd2c0741-0b27-4484-a927-b5e53c8f715c)')
    );
    await waitForNextUpdate();

    const [, saveComment] = result.current;

    await act(async () => saveComment('', 'julia@akeneo.com'));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.discussion.comment_submit_error'
    );
    const [, , validationErrors] = result.current;
    expect(validationErrors).toStrictEqual(backendValidationErrors);
});

test('it renders an error notification if the submitted comment is longer than 255 characters', async () => {
    const backendValidationErrors = [
        {
            propertyPath: 'content',
            message: 'The comment should not exceed 255 characters.',
            invalidValue:
                "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets.",
        },
    ];

    global.fetch = jest
        .fn()
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => backendResponse,
        }))
        .mockImplementationOnce(async () => ({
            ok: false,
            json: async () => backendValidationErrors,
        }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() =>
        useProductFile('cd2c0741-0b27-4484-a927-b5e53c8f715c)')
    );
    await waitForNextUpdate();

    const [, saveComment] = result.current;

    await act(async () =>
        saveComment(
            "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets.",
            'julia@akeneo.com'
        )
    );

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.discussion.comment_submit_error'
    );
    const [, , validationErrors] = result.current;
    expect(validationErrors).toStrictEqual(backendValidationErrors);
});

test('it renders an error notification if there is already 50 comments on the product file and we try to submit another one.', async () => {
    const backendValidationErrors = [
        {
            propertyPath: 'content',
            message: 'The product file cannot have more than 50 comments.',
            invalidValue: "This comment won't be created.",
        },
    ];

    global.fetch = jest
        .fn()
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => backendResponse,
        }))
        .mockImplementationOnce(async () => ({
            ok: false,
            json: async () => backendValidationErrors,
        }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() =>
        useProductFile('cd2c0741-0b27-4484-a927-b5e53c8f715c)')
    );
    await waitForNextUpdate();

    const [, saveComment] = result.current;

    await act(async () => saveComment("This comment won't be created.", 'julia@akeneo.com'));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.discussion.comment_submit_error'
    );
    const [, , validationErrors] = result.current;
    expect(validationErrors).toStrictEqual(backendValidationErrors);
});
