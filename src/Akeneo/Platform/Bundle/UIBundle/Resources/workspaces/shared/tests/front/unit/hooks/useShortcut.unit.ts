'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useShortcut} from '../../../../src/hooks/useShortcut';
import {renderHook} from '@testing-library/react-hooks';

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
