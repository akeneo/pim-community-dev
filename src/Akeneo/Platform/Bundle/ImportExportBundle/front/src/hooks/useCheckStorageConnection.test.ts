import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useCheckStorageConnection} from './useCheckStorageConnection';
import {SftpStorage} from '../models';

test('connection healthy', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
  }));

  const storage: SftpStorage = {
    type: 'sftp',
    file_path: 'test.xlsx',
    host: '127.0.0.1',
    port: 22,
    login_type: 'password',
    username: 'sftp',
    password: 'password',
  };
  const {result} = renderHookWithProviders(() => useCheckStorageConnection('csv_product_export', storage));
  const [, , checkReliability] = result.current;

  await act(async () => {
    await checkReliability();
  });

  const [isValid, canCheckConnection] = result.current;

  expect(isValid).toEqual(true);
  expect(canCheckConnection).toEqual(false);
});

test('connection not healthy returns false', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));

  const storage: SftpStorage = {
    type: 'sftp',
    file_path: 'test.xlsx',
    host: '127.0.0.1',
    port: 22,
    login_type: 'password',
    username: 'sftp',
    password: 'password',
  };
  const {result} = renderHookWithProviders(() => useCheckStorageConnection('csv_product_export', storage));
  const [, , checkReliability] = result.current;

  await act(async () => {
    await checkReliability();
  });

  const [isValid, canCheckConnection] = result.current;

  expect(isValid).toEqual(false);
  expect(canCheckConnection).toEqual(true);
});
