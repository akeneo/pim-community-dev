import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useStoredJobExecutionFilter} from './useStoredJobExecutionFilter';
import {getDefaultJobExecutionFilter} from '../models';

beforeEach(() => {
  localStorage.clear();
});

test('it returns the default filter when local storage is empty', () => {
  const {result} = renderHookWithProviders(() => useStoredJobExecutionFilter());

  expect(result.current[0]).toEqual(getDefaultJobExecutionFilter());
});

test('it returns the stored filter in local storage', () => {
  const filter = {
    ...getDefaultJobExecutionFilter(),
    user: ['julia', 'peter'],
  };
  localStorage.setItem('process-tracker.filters', JSON.stringify(filter));

  const {result} = renderHookWithProviders(() => useStoredJobExecutionFilter());

  expect(result.current[0]).toEqual(filter);
});

test('it merges the default filter with the stored local storage', () => {
  let filter = getDefaultJobExecutionFilter() as any;

  delete filter.user;
  delete filter.sort;

  localStorage.setItem('process-tracker.filters', JSON.stringify(filter));

  const {result} = renderHookWithProviders(() => useStoredJobExecutionFilter());

  expect(result.current[0]).toEqual(getDefaultJobExecutionFilter());
});
