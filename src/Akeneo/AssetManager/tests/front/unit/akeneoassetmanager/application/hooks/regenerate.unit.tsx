'use strict';

import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';

describe('Test regenerate hook', () => {
  test('It fetches the url using POST and set itself to false when done', async () => {
    global.fetch = jest.fn().mockImplementation(() => Promise.resolve());

    const url = 'https://akeneo.com/image.jpg';
    const {result} = renderHook(() => useRegenerate(url));
    let [regenerate, doRegenerate] = result.current;

    await act(async () => {
      await doRegenerate();
    });

    expect(global.fetch).toHaveBeenCalledTimes(1);
    expect(global.fetch).toHaveBeenCalledWith(url, {cache: 'no-cache', method: 'POST'});
    expect(regenerate).toEqual(false);

    global.fetch.mockClear();
    delete global.fetch;
  });
});
