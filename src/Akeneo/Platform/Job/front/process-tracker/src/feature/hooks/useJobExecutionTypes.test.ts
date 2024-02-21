import {useJobExecutionTypes} from './useJobExecutionTypes';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionTypes: string[] = ['import', 'export', 'mass_edit'];

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionTypes,
  }));
});

test('It fetches job execution types', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTypes());
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedJobExecutionTypes);
});

test('It returns job execution types only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionTypes());

  unmount();

  const jobExecutionTypes = result.current;

  expect(jobExecutionTypes).toEqual(null);
});
