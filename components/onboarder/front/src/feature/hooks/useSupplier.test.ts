import {renderHook, act} from '@testing-library/react-hooks';
import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useSupplier} from "./useSupplier";

const backendResponse = {
    identifier: "b2d485ef-49c4-45c8-b091-db0243b76055",
    code: "tes",
    label: "supplier test",
    contributors: [
        "aa@aa.aa",
        "bb@bb.bb",
        "cc@cc.cc",
    ]
}

test('it returns the supplier only when the data are loaded', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    expect(result.current.supplier).toBeNull();
    expect(result.current.supplierHasChanges()).toBe(false);

    await waitForNextUpdate();

    expect(result.current.supplier).toEqual(backendResponse);
    expect(result.current.supplierHasChanges()).toBe(false);
});

test('it returns that the supplier has changes when the label is updated', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();
    await act(async () => result.current.setSupplierLabel('updated label'));

    expect(result.current.supplier?.label).toBe('updated label');
    expect(result.current.supplierHasChanges()).toBe(true);
});

test('it returns that the supplier has changes when the contributors are updated', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();
    await act(async () => result.current.setSupplierContributors(['dd@dd.dd']));

    expect(result.current.supplier?.contributors).toEqual(['dd@dd.dd']);
    expect(result.current.supplierHasChanges()).toBe(true);
});

test('it saves a supplier', async () => {
    mockSuccessfulBackendResponse();
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();
    await act(async () => result.current.setSupplierLabel('updated label'));
    await act(async () => result.current.saveSupplier());

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.SUCCESS,
        'onboarder.supplier.supplier_edit.sucess_message',
    );
});

test('it renders an error notification if the saving of the supplier failed', async () => {
    //Loading => OK
    //Saving = KO
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: true,
        json: async () => backendResponse,
    })).mockImplementationOnce(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();
    await act(async () => result.current.setSupplierLabel('updated label'));
    await act(async () => result.current.saveSupplier());

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'onboarder.supplier.supplier_edit.unknown_error',
    );
});

test('it renders an error notification if the loading of the supplier failed', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await renderHookWithProviders(() => useSupplier('id1'));

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'onboarder.supplier.supplier_edit.error'
    );
});

function mockSuccessfulBackendResponse() {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));
}
