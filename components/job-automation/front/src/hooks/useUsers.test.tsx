import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useUsers} from './useUsers';
import TestRenderer from 'react-test-renderer';

const {act} = TestRenderer;

test('it return fetched users', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {id: 1, username: 'Admin'},
      {id: 2, username: 'Julia'},
    ],
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useUsers());

  await waitForNextUpdate();

  expect(result.current.availableUsers).toEqual([
    {id: 1, username: 'Admin'},
    {id: 2, username: 'Julia'},
  ]);
  expect(global.fetch).toBeCalledWith('pimee_job_automation_get_users', {
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
      {id: 1, username: 'Admin'},
      {id: 2, username: 'Julia'},
    ],
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useUsers());
  await waitForNextUpdate();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {id: 3, username: 'Jean'},
      {id: 4, username: 'Michel'},
    ],
  }));

  const loadNexPage = result.current.loadNextPage;
  await act(async () => await loadNexPage());

  expect(result.current.availableUsers).toEqual([
    {id: 1, username: 'Admin'},
    {id: 2, username: 'Julia'},
    {id: 3, username: 'Jean'},
    {id: 4, username: 'Michel'},
  ]);
});
