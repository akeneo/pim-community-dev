import {renderHook} from '@testing-library/react-hooks';
import {act} from '@testing-library/react';
import {useAutoFocus} from './useAutoFocus';

const button = document.createElement('button');
document.body.appendChild(button);

const ref = {
  current: button,
};

afterEach(() => {
  button.blur();
});

test('It sets automatically the focus on the given ref', () => {
  renderHook(() => useAutoFocus(ref));
  expect(button).toHaveFocus();
});

test('I can request the focus on the given ref', () => {
  const {result} = renderHook(() => useAutoFocus(ref));
  const focus = result.current;

  expect(button).toHaveFocus();

  act(() => {
    button.blur();
  });

  expect(button).not.toHaveFocus();

  act(() => {
    focus();
  });

  expect(button).toHaveFocus();
});

test('It does not try to focus if the current ref is null', () => {
  const ref = {
    current: null,
  };

  renderHook(() => useAutoFocus(ref));
  expect(button).not.toHaveFocus();
});
