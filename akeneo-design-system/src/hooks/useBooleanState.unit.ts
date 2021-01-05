import {act} from 'react-test-renderer';
import {renderHook} from '@testing-library/react-hooks';
import {useBooleanState} from './useBooleanState';

test('It manages true default value', () => {
  const {result} = renderHook(() => useBooleanState(true));

  const [isOpen] = result.current;

  expect(isOpen).toEqual(true);
});
test('It manages false default value', () => {
  const {result} = renderHook(() => useBooleanState(false));
  const [isOpen] = result.current;

  expect(isOpen).toEqual(false);
});

test('It manages opening and closing', () => {
  const {result} = renderHook(() => useBooleanState());

  let [isOpen] = result.current;
  const [, open, close] = result.current;
  expect(isOpen).toEqual(false);
  void act(() => {
    open();
  });
  [isOpen] = result.current;
  expect(isOpen).toEqual(true);
  void act(() => {
    close();
  });
  [isOpen] = result.current;
  expect(isOpen).toEqual(false);
});
