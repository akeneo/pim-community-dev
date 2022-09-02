import {act} from '@testing-library/react-hooks';
import {channels, renderHookWithProviders} from 'feature/tests';
import {useChannels} from './useChannels';

test('It fetches channels', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useChannels());

  await act(async () => {
    await waitForNextUpdate();
  });

  const attributes = result.current;
  expect(attributes).toEqual(channels);
});

test('It does not set state when unmounted', () => {
  const {result, unmount} = renderHookWithProviders(() => useChannels());

  unmount();

  const attributes = result.current;
  expect(attributes).toEqual([]);
});
