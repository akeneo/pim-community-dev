'use strict';

//TODO move this test to new package @akeneo-pim-community/shared
import '@testing-library/jest-dom/extend-expect';
import {useAutoFocus} from '@akeneo-pim-community/shared/hooks/useAutoFocus';
import {renderHook} from '@testing-library/react-hooks';
import {act} from '@testing-library/react';

test('It sets automatically the focus on the given ref', async () => {
  const mockFocus = jest.fn();
  const ref = {
    current: {
      focus: mockFocus,
    },
  };

  // @ts-ignore
  renderHook(() => useAutoFocus(ref));
  expect(mockFocus).toHaveBeenCalledTimes(1);
});

test('I can request the focus on the given ref', async () => {
  const mockFocus = jest.fn();
  const ref = {
    current: {
      focus: mockFocus,
    },
  };

  // @ts-ignore
  const {result} = renderHook(() => useAutoFocus(ref));
  const focus = result.current;

  act(() => {
    focus();
  });

  expect(mockFocus).toHaveBeenCalledTimes(2);
});
