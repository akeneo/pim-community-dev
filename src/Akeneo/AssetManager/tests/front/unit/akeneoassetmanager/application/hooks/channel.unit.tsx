'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {renderHook, act} from '@testing-library/react-hooks';

const channelFetcher = {
  fetchAll: () =>
    new Promise(resolve => {
      act(() => {
        setTimeout(() => resolve([{code: 'en_US'}]), 100);
      });
    }),
};

describe('Test channel hooks', () => {
  test('It can fetch the channels', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useChannels(channelFetcher));

    expect(result.current).toEqual([]);

    await waitForNextUpdate();

    expect(result.current).toEqual([{code: 'en_US'}]);
  });
});
