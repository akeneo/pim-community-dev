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
    filePath: '',
  });

  expect(getDefaultStorage('sftp')).toEqual({
    type: 'sftp',
    filePath: '',
    host: '',
    username: '',
    password: '',
  });

  expect(getDefaultStorage('none')).toEqual({
    type: 'none',
  });

  // @ts-expect-error invalid storage type
  expect(() => getDefaultStorage('invalid')).toThrowError('Unknown storage type: invalid');
});
