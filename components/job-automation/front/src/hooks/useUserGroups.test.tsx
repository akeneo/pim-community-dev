import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useUserGroups} from './useUserGroups';

test('it return fetched user groups', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [{'id': 1, 'label': 'IT Support'}, {'id': 2, 'label': 'Manager'}],
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useUserGroups());

  await waitForNextUpdate();

  expect(result.current.availableUserGroups).toEqual([{'id': 1, 'label': 'IT Support'}, {'id': 2, 'label': 'Manager'}]);
  expect(global.fetch).toBeCalledWith('pimee_job_automation_get_user_groups', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    method: 'GET',
  });
});
