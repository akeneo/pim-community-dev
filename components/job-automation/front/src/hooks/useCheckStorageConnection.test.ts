import {renderHookWithProviders} from '@akeneo-pim-community/shared/lib/tests/utils';
import {useCheckStorageConnection} from './useCheckStorageConnection';
import {SftpStorage} from '../components';
import {act} from '@testing-library/react-hooks';

test('connection healthy', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => ({
      is_connection_healthy: true,
    }),
  }));

  const storage: SftpStorage = {
    type: 'sftp',
    file_path: 'test.xlsx',
    host: '127.0.0.1',
    port: 22,
    username: 'sftp',
    password: 'password',
  };
  const {result} = renderHookWithProviders(() => useCheckStorageConnection(storage));
  const checkReliability = result.current[2];

  await act(async () => {
    await checkReliability();
  });

  expect(result.current[0]).toEqual(true);
  expect(result.current[1]).toEqual(false);
});

test('connection not healthy return error_message', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: 'test.xlsx',
    host: '127.0.0.1',
    port: 22,
    username: 'sftp',
    password: 'password',
  };
  const {result} = renderHookWithProviders(() => useCheckStorageConnection(storage));
  const checkReliability = result.current[2];

  await act(async () => {
    await checkReliability();
  });

  expect(result.current[0]).toEqual(false);
  expect(result.current[1]).toEqual(false);
});
