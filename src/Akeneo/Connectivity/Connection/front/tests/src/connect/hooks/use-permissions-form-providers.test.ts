import {renderHook} from '@testing-library/react-hooks';
import {usePermissionFormRegistry} from '@src/shared/permission-form-registry';
import usePermissionsFormProviders from '@src/connect/hooks/use-permissions-form-providers';
import {NotificationLevel} from '@src/shared/notify';

beforeEach(() => {
    jest.clearAllMocks();
});

jest.mock('@src/shared/permission-form-registry', () => ({
    ...jest.requireActual('@src/shared/permission-form-registry'),
    usePermissionFormRegistry: jest.fn(() => {
        return {
            all: () => Promise.resolve([]),
        };
    }),
}));

const notify = jest.fn();

jest.mock('@src/shared/notify', () => ({
    ...jest.requireActual('@src/shared/notify'),
    useNotify: jest.fn(() => notify),
}));

test('It fetches providers and saved permissions', async done => {
    const providers = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(() =>
                Promise.resolve({
                    view: {
                        all: false,
                        identifiers: ['code1'],
                    },
                })
            ),
        },
    ];

    (usePermissionFormRegistry as jest.Mock).mockImplementation(() => ({
        all: () => Promise.resolve(providers),
    }));

    const {result, waitForNextUpdate} = renderHook(() => usePermissionsFormProviders('redactor'));

    expect(result.current).toEqual([null, {}, expect.any(Function)]);

    await waitForNextUpdate();

    expect(result.current).toEqual([
        [
            {
                key: 'providerKey1',
                label: 'Provider1',
                renderForm: expect.any(Function),
                renderSummary: expect.any(Function),
                save: expect.any(Function),
                loadPermissions: expect.any(Function),
            },
        ],
        {
            providerKey1: {
                view: {
                    all: false,
                    identifiers: ['code1'],
                },
            },
        },
        expect.any(Function),
    ]);

    done();
});

test('It notifies an error when loading saved permissions fails', async done => {
    const providers = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn().mockRejectedValue('some error occured'),
        },
    ];

    (usePermissionFormRegistry as jest.Mock).mockImplementation(() => ({
        all: () => Promise.resolve(providers),
    }));

    const {result, waitForNextUpdate} = renderHook(() => usePermissionsFormProviders('redactor'));

    expect(result.current).toEqual([null, {}, expect.any(Function)]);

    await waitForNextUpdate();

    expect(result.current).toEqual([
        [
            {
                key: 'providerKey1',
                label: 'Provider1',
                renderForm: expect.any(Function),
                renderSummary: expect.any(Function),
                save: expect.any(Function),
                loadPermissions: expect.any(Function),
            },
        ],
        {
            providerKey1: false,
        },
        expect.any(Function),
    ]);

    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.load_permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.load_permissions_error.title?entity=Provider1',
        }
    );

    done();
});
