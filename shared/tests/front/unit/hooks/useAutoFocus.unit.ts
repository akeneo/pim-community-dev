import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {act} from '@testing-library/react';
import {useAutoFocus} from '../../../../src/hooks/useAutoFocus';

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

test('It does not try to focus if the current ref is null', async () => {
  const mockFocus = jest.fn();
  const ref = {
    current: null,
  };

  // @ts-ignore
  renderHook(() => useAutoFocus(ref));
  expect(mockFocus).not.toHaveBeenCalled();
});
