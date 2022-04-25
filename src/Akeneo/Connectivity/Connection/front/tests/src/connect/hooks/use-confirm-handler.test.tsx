import {renderHook, act} from '@testing-library/react-hooks';
import {useNotify} from '@src/shared/notify';
import {NotificationLevel} from '@src/shared/notify';
import {useConfirmHandler} from '@src/connect/hooks/use-confirm-handler';
import {useConfirmAuthorization} from '@src/connect/hooks/use-confirm-authorization';
import {waitFor} from '@testing-library/react';

/*eslint-disable */
declare global {
    namespace NodeJS {
        interface Global {
            window: any;
        }
    }
}
/*eslint-enable */

jest.mock('@src/shared/notify');
jest.mock('@src/connect/hooks/use-confirm-authorization');

const notify = jest.fn();

beforeEach(() => {
    jest.clearAllMocks();

    delete global.window.location;
    global.window = Object.create(window);
    global.window.location = {
        assign: jest.fn(),
    };
});

test('it notifies when there is an error during the API request', async () => {
    const confirmAuthorization = jest.fn(() =>
        Promise.reject({
            status: 500,
            statusText: 'Server Internal error',
            errors: [],
        })
    );

    (useNotify as jest.Mock).mockImplementation(() => notify);
    (useConfirmAuthorization as jest.Mock).mockImplementation(() => confirmAuthorization);

    const {result, waitForNextUpdate} = renderHook(() => useConfirmHandler('client id', [], []));
    expect(typeof result.current.confirm).toBe('function');
    expect(result.current.processing).toBe(false);

    act(() => {
        result.current.confirm();
    });

    expect(result.current.processing).toBe(true);

    await waitForNextUpdate();

    expect(result.current.processing).toBe(false);

    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.error'
    );
});

test('it notifies when there is a specific error during the API request', async () => {
    const confirmAuthorization = jest.fn(() =>
        Promise.reject({
            status: 400,
            statusText: 'Bad request',
            errors: [{message: 'Specific Error Message', property_path: ''}],
        })
    );

    (useNotify as jest.Mock).mockImplementation(() => notify);
    (useConfirmAuthorization as jest.Mock).mockImplementation(() => confirmAuthorization);

    const {result, waitForNextUpdate} = renderHook(() => useConfirmHandler('client id', [], []));
    expect(typeof result.current.confirm).toBe('function');
    expect(result.current.processing).toBe(false);

    act(() => {
        result.current.confirm();
    });

    expect(result.current.processing).toBe(true);

    await waitForNextUpdate();

    expect(result.current.processing).toBe(false);

    expect(notify).toBeCalledWith(NotificationLevel.ERROR, 'Specific Error Message');
});

test('it redirects when the confirmation succeeded', async () => {
    const confirmAuthorization = jest.fn(
        () =>
            new Promise(resolve =>
                setTimeout(
                    () =>
                        resolve({
                            userGroup: 'admin',
                            appId: '9ca0e85c-6264-11ec-90d6-0242ac120003',
                            redirectUrl: 'https://example.com',
                        }),
                    100
                )
            )
    );

    (useNotify as jest.Mock).mockImplementation(() => notify);
    (useConfirmAuthorization as jest.Mock).mockImplementation(() => confirmAuthorization);

    const provider1 = {
        save: jest.fn(),
        key: 'provider_1',
        label: 'Provider 1 Label',
        renderForm: jest.fn(),
        renderSummary: jest.fn(),
        loadPermissions: jest.fn(),
    };
    const provider2 = {
        save: jest.fn(),
        key: 'provider_2',
        label: 'Provider 2 Label',
        renderForm: jest.fn(),
        renderSummary: jest.fn(),
        loadPermissions: jest.fn(),
    };

    const {result, waitForNextUpdate} = renderHook(() =>
        useConfirmHandler('client id', [provider1, provider2], {provider_1: 'foo'})
    );
    expect(typeof result.current.confirm).toBe('function');
    expect(result.current.processing).toBe(false);

    act(() => {
        result.current.confirm();
    });

    expect(result.current.processing).toBe(true);

    await waitFor(() => expect(global.window.location.assign).toBeCalled());

    expect(provider1.save).toBeCalledWith('admin', 'foo');
    expect(provider2.save).not.toBeCalled();

    expect(notify).toBeCalledTimes(1);
    expect(notify).toBeCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.apps.wizard.flash.success'
    );
});
