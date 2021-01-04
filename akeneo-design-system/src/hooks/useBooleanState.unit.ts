import {act} from 'react-test-renderer';
import {renderHook} from '@testing-library/react-hooks';
import {useBooleanState} from './useBooleanState';

test('It manages false default value', async () => {
  const {result} = renderHook(() => useBooleanState(true));

  const [isOpen] = result.current;

  await expect(isOpen).toEqual(true);
});
test('It manages true default value', async () => {
  const {result} = renderHook(() => useBooleanState(false));
  const [isOpen] = result.current;

  await expect(isOpen).toEqual(false);
});

test('It manages opening and closing', async () => {
  let {result} = renderHook(() => useBooleanState());

  let [isOpen, open, close] = result.current;
  await expect(isOpen).toEqual(false);
  act(() => {
    open();
  });
  [isOpen] = result.current;
  await expect(isOpen).toEqual(true);
  act(() => {
    close();
  });
  [isOpen] = result.current;
  await expect(isOpen).toEqual(false);
});
