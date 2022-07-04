import {FeatureFlags} from '@akeneo-pim-community/shared';
import {isValidStorageType, getDefaultStorage, isExport, getDefaultFilePath} from './model';

const featureFlagCollection = {
  job_automation_local_storage: false,
  job_automation_remote_storage: false,
};

const enableFeatureFlag = (featureFlag: string) => (featureFlagCollection[featureFlag] = true);

const featureFlags: FeatureFlags = {
  isEnabled: (featureFlag: string) => featureFlagCollection[featureFlag],
};

test('it says if a storage type is valid', () => {
  expect(isValidStorageType('local', featureFlags)).toBe(false);
  expect(isValidStorageType('sftp', featureFlags)).toBe(false);

  enableFeatureFlag('job_automation_local_storage');
  enableFeatureFlag('job_automation_remote_storage');

  expect(isValidStorageType('none', featureFlags)).toBe(true);
  expect(isValidStorageType('local', featureFlags)).toBe(true);
  expect(isValidStorageType('sftp', featureFlags)).toBe(true);
  expect(isValidStorageType('invalid', featureFlags)).toBe(false);
});

test('it returns the default local storage', () => {
  expect(getDefaultStorage('export', 'local', 'xlsx')).toEqual({
    type: 'local',
    file_path: 'export_%job_label%_%datetime%.xlsx',
  });

  expect(getDefaultStorage('import', 'sftp', 'csv')).toEqual({
    type: 'sftp',
    file_path: 'myfile.csv',
    host: '',
    port: 22,
    username: '',
    password: '',
  });

  expect(getDefaultStorage('export', 'none', 'xlsx')).toEqual({
    type: 'none',
  });

  // @ts-expect-error invalid storage type
  expect(() => getDefaultStorage('export', 'invalid', 'xlsx')).toThrowError('Unknown storage type: invalid');
});

test('it says if a job is an export', () => {
  expect(isExport('export')).toBe(true);
  expect(isExport('import')).toBe(false);
});

test('it returns the default file path', () => {
  expect(getDefaultFilePath('export', 'xlsx')).toBe('export_%job_label%_%datetime%.xlsx');
  expect(getDefaultFilePath('export', 'csv')).toBe('export_%job_label%_%datetime%.csv');
  expect(getDefaultFilePath('import', 'xlsx')).toBe('myfile.xlsx');
  expect(getDefaultFilePath('import', 'csv')).toBe('myfile.csv');
});
