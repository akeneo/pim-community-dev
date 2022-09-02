import React from 'react';
import {renderHook, act} from '@testing-library/react-hooks';
import {FetcherContext} from '../contexts';
import {useChannels} from './useChannels';
import {channels, fetchers} from '../tests';

const Wrapper: React.FC = ({children}) => {
  return <FetcherContext.Provider value={fetchers}>{children}</FetcherContext.Provider>;
};

test('It fetches channels', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useChannels(), {wrapper: Wrapper});

  await act(async () => {
    await waitForNextUpdate();
  });

  const attributes = result.current;
  expect(attributes).toEqual(channels);
});

test('It does not set state when unmounted', () => {
  const {result, unmount} = renderHook(() => useChannels(), {wrapper: Wrapper});

  unmount();

  const attributes = result.current;
  expect(attributes).toEqual([]);
});
