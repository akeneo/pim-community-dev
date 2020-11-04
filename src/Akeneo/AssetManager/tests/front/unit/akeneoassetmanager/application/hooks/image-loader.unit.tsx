'use strict';

import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import useImageLoader from 'akeneoassetmanager/application/hooks/image-loader';
import loadImage from 'akeneoassetmanager/tools/image-loader';

const resolveImmediatly = () => Promise.resolve();
const neverResolve = () => new Promise<void>(() => {});

jest.mock('akeneoassetmanager/tools/image-loader', () => ({
  __esModule: true,
  default: jest.fn().mockImplementation(resolveImmediatly),
}));

describe('Test useImageLoader hook', () => {
  test('There is no url if the loading is not terminated yet', () => {
    // This promise will never resolve, simulating an infinite loading time
    loadImage.mockImplementationOnce(neverResolve);
    const {result} = renderHook(() => useImageLoader('foo.jpg'));
    expect(result.current).toBeUndefined();
  });

  test('There is an url if the image is loaded', async () => {
    loadImage.mockImplementationOnce(resolveImmediatly);
    const {result, waitForNextUpdate} = renderHook(() => useImageLoader('foo.jpg'));
    await waitForNextUpdate();
    expect(result.current).toBe('foo.jpg');
  });

  test('The url is cleared after some time if the new one is not loaded fast enough', async () => {
    loadImage.mockImplementationOnce(resolveImmediatly);
    let url = 'foo.jpg';
    const {result, waitForNextUpdate, rerender} = renderHook(() => useImageLoader(url));
    await waitForNextUpdate();
    expect(result.current).toBe('foo.jpg');

    loadImage.mockImplementationOnce(neverResolve);
    url = 'bar.jpg';
    rerender();
    expect(result.current).toBe('foo.jpg');
    await waitForNextUpdate();
    expect(result.current).toBeUndefined();
  });
});
