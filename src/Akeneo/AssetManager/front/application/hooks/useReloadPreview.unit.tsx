import React from 'react';
import {renderHook, act} from '@testing-library/react-hooks';
import {ReloadPreviewProvider, useReloadPreview} from './useReloadPreview';

jest.useFakeTimers();

test('It returns reload preview value', () => {
  const {result} = renderHook(() => useReloadPreview(), {
    wrapper: ({children}) => <ReloadPreviewProvider>{children}</ReloadPreviewProvider>,
  });

  let [reloadPreview, toggleReload] = result.current;

  expect(reloadPreview).toEqual(false);

  act(() => {
    toggleReload();
  });

  [reloadPreview] = result.current;

  expect(reloadPreview).toEqual(true);

  act(() => {
    jest.runAllTimers();
  });

  [reloadPreview] = result.current;

  expect(reloadPreview).toEqual(false);
});

test('It throws when the context is undefined', () => {
  const {result} = renderHook(() => useReloadPreview());

  expect(result.error).toEqual(new Error('ReloadPreview context is not properly initialized'));
});
