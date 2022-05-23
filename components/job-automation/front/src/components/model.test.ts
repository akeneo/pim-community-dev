import {isValidStorageType, getDefaultStorage} from './model';

test('it says if a storage type is valid', () => {
  expect(isValidStorageType('none')).toBe(true);
  expect(isValidStorageType('local')).toBe(true);
  expect(isValidStorageType('sftp')).toBe(true);
  expect(isValidStorageType('invalid')).toBe(false);
});

test('it returns the default local storage', () => {
  expect(getDefaultStorage('export', 'local')).toEqual({
    type: 'local',
    file_path: '/tmp/export_%job_label%_%datetime%.xlsx',
  });

  expect(getDefaultStorage('export', 'sftp')).toEqual({
    type: 'sftp',
    file_path: 'export_%job_label%_%datetime%.xlsx',
    host: '',
    port: 22,
    username: '',
    password: '',
  });

  expect(getDefaultStorage('export', 'none')).toEqual({
    type: 'none',
  });

  // @ts-expect-error invalid storage type
  expect(() => getDefaultStorage('export', 'invalid')).toThrowError('Unknown storage type: invalid');
});
