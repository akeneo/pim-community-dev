import {isValidStorageType, getDefaultStorage} from './model';

test('it says if a storage type is valid', () => {
  expect(isValidStorageType('none')).toBe(true);
  expect(isValidStorageType('local')).toBe(true);
  expect(isValidStorageType('sftp')).toBe(true);
  expect(isValidStorageType('invalid')).toBe(false);
});

test('it returns the default local storage', () => {
  expect(getDefaultStorage('local')).toEqual({
    type: 'local',
    file_path: '',
  });

  expect(getDefaultStorage('sftp')).toEqual({
    type: 'sftp',
    file_path: '',
    host: '',
    port: 22,
    username: '',
    password: '',
  });

  expect(getDefaultStorage('none')).toEqual({
    type: 'none',
  });

  // @ts-expect-error invalid storage type
  expect(() => getDefaultStorage('invalid')).toThrowError('Unknown storage type: invalid');
});
