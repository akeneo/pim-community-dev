import {renderHookWithProviders} from "@akeneo-pim-community/shared/lib/tests/utils";
import {useCheckStorageConnection} from "./useCheckStorageConnection";
import {SftpStorage} from "../components";
import {act} from '@testing-library/react-hooks';

test('connection healthy', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => ({
            'is_connection_healthy': true,
        })
    }));

    const {result} = renderHookWithProviders(() => useCheckStorageConnection());
    const [check, setCheck, isChecking, checkReliability] = result.current;

    await act(async () => {
        await checkReliability({
            host: '127.0.0.1',
            port: 22,
            username: 'sftp',
            password: 'password'
        } as SftpStorage);
    })

    const [checkAfterCall, setCheckAfterCall, isCheckingAfterCall, checkReliabilityAfterCall] = result.current;

    expect(checkAfterCall).toEqual({
        'is_connection_healthy': true,
    })
    expect(isCheckingAfterCall).toEqual(false)
})

test('connection not healthy return error_message', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => ({
            'is_connection_healthy': false,
            'error_message': 'something got wrong'
        })
    }));

    const {result} = renderHookWithProviders(() => useCheckStorageConnection());
    const [check, setCheck, isChecking, checkReliability] = result.current;

    await act(async () => {
        await checkReliability({
            host: '127.0.0.1',
            port: 22,
            username: 'sftp',
            password: 'password'
        } as SftpStorage);
    })

    const [checkAfterCall, setCheckAfterCall, isCheckingAfterCall, checkReliabilityAfterCall] = result.current;

    expect(checkAfterCall).toEqual({
        'is_connection_healthy': false,
        'error_message': 'something got wrong'
    })
    expect(isCheckingAfterCall).toEqual(false)
})