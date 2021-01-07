'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useFocus} from 'akeneoassetmanager/application/hooks/input';
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
});
