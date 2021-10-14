import {renderHook} from '@testing-library/react-hooks';
import {usePermissionFormRegistry} from '@src/shared/permission-form-registry';
import useLoadPermissionsFormProviders from '@src/connect/hooks/use-load-permissions-form-providers';

jest.mock('@src/shared/permission-form-registry', () => ({
    ...jest.requireActual('@src/shared/permission-form-registry'),
    usePermissionFormRegistry: jest.fn(() => {
        return {
            all: () => Promise.resolve([]),
        };
    }),
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

    const {result, waitForNextUpdate} = renderHook(() => useLoadPermissionsFormProviders('redactor'));

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
