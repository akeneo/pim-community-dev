import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {useCheckInstanceCanBeReset} from './useCheckInstanceCanBeReset';
import {act} from 'react-test-renderer';

const flushPromises = () => new Promise(setImmediate);

test('it can check if the PIM can be reset', async () => {
  fetchMock.mockResponseOnce('', {
    status: 200,
  });

  const {result} = renderHookWithProviders(useCheckInstanceCanBeReset);
  const [isLoading, checkInstanceCanBeReset] = result.current;

  expect(isLoading).toBe(false);
  act(() => {
    checkInstanceCanBeReset();
  });

  expect(result.current[0]).toBe(true);
  await act(async () => {
    await flushPromises();
  });

  expect(result.current[0]).toBe(false);
});

test('it throws an error when the PIM cannot be reset', async () => {
  fetchMock.mockResponseOnce('', {status: 400, statusText: 'Cannot reset the PIM'});
  const {result} = renderHookWithProviders(useCheckInstanceCanBeReset);
  const [, checkInstanceCanBeReset] = result.current;

  await expect(async () => {
    await act(async () => await checkInstanceCanBeReset());
  }).rejects.toThrow(Error);
});
