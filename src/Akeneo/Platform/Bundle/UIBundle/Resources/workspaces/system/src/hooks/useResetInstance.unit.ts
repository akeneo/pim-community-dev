import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {useResetInstance} from './useResetInstance';
import {act} from 'react-test-renderer';

const flushPromises = () => new Promise(setImmediate);

test('it launch a reset instance', async () => {
  fetchMock.mockResponseOnce(async () => JSON.stringify([]), {
    status: 200,
  });

  const {result} = renderHookWithProviders(useResetInstance);
  const [isLoading, resetInstance] = result.current;

  expect(isLoading).toBe(false);
  act(() => {
    resetInstance();
  });

  expect(result.current[0]).toBe(true);
  await act(async () => {
    await flushPromises();
  });

  expect(result.current[0]).toBe(false);
});

test('it throw an error when an error occurred during the reset instance', async () => {
  fetchMock.mockResponseOnce('', {status: 500, statusText: 'Internal Error'});
  const {result} = renderHookWithProviders(useResetInstance);
  const [, resetInstance] = result.current;

  await expect(async () => {
    await act(async () => await resetInstance());
  }).rejects.toThrow(Error);
});
