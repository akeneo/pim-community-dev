import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useUserGroups} from './useUserGroups';
import TestRenderer from 'react-test-renderer';

const {act} = TestRenderer;

test('it return fetched user groups', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {id: 1, label: 'IT Support'},
      {id: 2, label: 'Manager'},
    ],
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useUserGroups());

  await waitForNextUpdate();

  expect(result.current.availableUserGroups).toEqual([
    {id: 1, label: 'IT Support'},
    {id: 2, label: 'Manager'},
  ]);
  expect(global.fetch).toBeCalledWith('pimee_job_automation_get_user_groups', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    method: 'GET',
  });
});

test('it load next page', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {id: 1, label: 'IT Support'},
      {id: 2, label: 'Manager'},
    ],
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useUserGroups());
  await waitForNextUpdate();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {id: 3, label: 'Redactor'},
      {id: 4, label: 'QA'},
    ],
  }));

  const loadNexPage = result.current.loadNextPage;
  await act(async () => await loadNexPage());

  expect(result.current.availableUserGroups).toEqual([
    {id: 1, label: 'IT Support'},
    {id: 2, label: 'Manager'},
    {id: 3, label: 'Redactor'},
    {id: 4, label: 'QA'},
  ]);
});
