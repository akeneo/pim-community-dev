import {useJobExecutionUsers} from './useJobExecutionUsers';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionUsers: string[] = ['peter', 'mary'];

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionUsers,
  }));
});

test('It fetches job execution users', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionUsers());
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedJobExecutionUsers);
});

test('It returns job execution users only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionUsers());

  unmount();

  const jobExecutionUsers = result.current;

  expect(jobExecutionUsers).toEqual(null);
});
