'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useFocus, useShortcut} from 'akeneoassetmanager/application/hooks/input';
import {renderHook} from '@testing-library/react-hooks';

describe('Test input hooks', () => {
  test('It sets focus on the given ref', async () => {
    let focusHasBeenCalled = false;
    const input = {
      focus: () => {
        focusHasBeenCalled = true;
      },
    };

    const {result} = renderHook(() => useFocus());
    expect(result.current[0]).toEqual({current: null});

    result.current[0].current = input;
    result.current[1]();

    expect(result.current[0]).toEqual({current: input});
    expect(focusHasBeenCalled).toEqual(true);
  });

  test('It can register listener on keyboard events', async () => {
    let callbackHasBeenCalled = false;

    renderHook(() =>
      useShortcut(' ', () => {
        callbackHasBeenCalled = true;
      })
    );
    document.dispatchEvent(new KeyboardEvent('keydown', {code: ' '}));

    expect(callbackHasBeenCalled).toEqual(true);
  });

  test('It does nothing if the key does not match', async () => {
    let callbackHasBeenCalled = false;

    renderHook(() =>
      useShortcut('Enter', () => {
        callbackHasBeenCalled = true;
      })
    );
    document.dispatchEvent(new KeyboardEvent('keydown', {code: ' '}));

    expect(callbackHasBeenCalled).toEqual(false);
  });
});
