import {useSuppliers} from './useSuppliers';
import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';

const backendResponse = {
    suppliers: [
        {identifier: 'b2d485ef-49c4-45c8-b091-db0243b76055', code: 'code', label: 'label', contributorsCount: 1},
        {
            identifier: 'c15a23f4-5c9f-8f1d-5f8a-db0243b76051',
            code: 'another_code',
            label: 'another_label',
            contributorsCount: 3,
        },
    ],
    total: 2,
    items_per_page: 50,
};

test('it loads the suppliers', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSuppliers('', 1));

    expect(result.current[0]).toEqual([]);

    await waitForNextUpdate();

    expect(result.current[0]).toBe(backendResponse.suppliers);
    expect(result.current[1]).toBe(backendResponse.total);
});

test('it renders an error notification if the loading of the suppliers failed', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await renderHookWithProviders(() => useSuppliers('', 1));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.supplier.supplier_list.notification.error.title',
        'supplier_portal.supplier.supplier_list.notification.error.content'
    );
});
