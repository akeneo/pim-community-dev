'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useShortcut} from '../../../../src/hooks/useShortcut';
import {renderHook} from '@testing-library/react-hooks';
import {Key} from '@akeneo-pim-community/shared';

test('It can register listener on keyboard events', async () => {
  let callbackHasBeenCalled = false;

  renderHook(() =>
    useShortcut(Key.Space, () => {
      callbackHasBeenCalled = true;
    })
  );
  document.dispatchEvent(new KeyboardEvent('keydown', {code: ' '}));

  expect(callbackHasBeenCalled).toEqual(true);
});

test('It does nothing if the key does not match', async () => {
  let callbackHasBeenCalled = false;

  renderHook(() =>
    useShortcut(Key.Enter, () => {
      callbackHasBeenCalled = true;
    })
  );
  document.dispatchEvent(new KeyboardEvent('keydown', {code: ' '}));

  expect(callbackHasBeenCalled).toEqual(false);
});
