import {act} from '@testing-library/react-hooks';
import {mockedDependencies, NotificationLevel, renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useSupplier} from './useSupplier';
import {waitFor} from '@testing-library/react';
import {Supplier} from '../models';

const backendResponse = {
    identifier: 'b2d485ef-49c4-45c8-b091-db0243b76055',
    code: 'test',
    label: 'supplier test',
    contributors: ['aa@aa.aa', 'bb@bb.bb', 'cc@cc.cc'],
};

test('it returns the supplier when the data are loaded', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await act(async () => await waitForNextUpdate());

    const [supplier, , supplierHasChanges] = result.current;
    expect(supplier).toEqual(backendResponse);
    expect(supplierHasChanges).toBe(false);
});

test('it returns that the supplier has changes when the label is updated', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();

    const [supplier, setSupplier] = result.current;

    await act(async () => setSupplier({...supplier, label: 'Jean Michel'}));

    waitForNextUpdate();

    const [updatedSupplier, , updatedSupplierHasChanges] = result.current;

    expect(updatedSupplier.label).toBe('Jean Michel');
    expect(updatedSupplierHasChanges).toBe(true);
});

test('it returns that the supplier has changes when the contributors are updated', async () => {
    mockSuccessfulBackendResponse();

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();

    const [supplier, setSupplier] = result.current;

    await act(async () => setSupplier({...supplier, contributors: ['michel@michel.com']}));

    waitForNextUpdate();

    const [updatedSupplier, , updatedSupplierHasChanges] = result.current;

    expect(updatedSupplier.contributors).toEqual(['michel@michel.com']);
    expect(updatedSupplierHasChanges).toBe(true);
});

test('it saves a supplier', async () => {
    mockSuccessfulBackendResponse();
    const notify = jest.spyOn(mockedDependencies, 'notify');

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();

    const [supplier, setSupplier, , saveSupplier] = result.current;

    await act(async () => setSupplier({...supplier, label: 'new label'}));
    await act(async () => saveSupplier());

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.SUCCESS,
        'supplier_portal.supplier.supplier_edit.success_message'
    );
});

test('it renders an error notification if the saving of the supplier failed', async () => {
    const backendValidationErrors = [
        {
            propertyPath: 'label',
            message: 'This value is too long. It should have 3 characters or less.',
            invalidValue: 'Jean Michel',
        },
    ];

    //Loading => OK
    //Saving = KO
    // @ts-ignore
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

    const {result, waitForNextUpdate} = renderHookWithProviders(() => useSupplier('id1'));
    await waitForNextUpdate();

    const [supplier, setSupplier, , saveSupplier] = result.current;

    const updatedSupplier: Supplier = {
        ...supplier,
        label: 'Jean Michel',
        contributors: [...supplier.contributors, 'invalidEmail'],
    };
    await act(async () => setSupplier(updatedSupplier));
    await act(async () => saveSupplier());

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.supplier.supplier_edit.update_error'
    );
    const [, , , , validationErrors] = result.current;
    expect(validationErrors).toStrictEqual(backendValidationErrors);
});

test('it renders an error notification if the loading of the supplier failed', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await renderHookWithProviders(() => useSupplier('id1'));

    await waitFor(() => {
        expect(notify).toHaveBeenNthCalledWith(
            1,
            NotificationLevel.ERROR,
            'supplier_portal.supplier.supplier_edit.loading_error'
        );
    });
});

function mockSuccessfulBackendResponse() {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => backendResponse,
    }));
}
