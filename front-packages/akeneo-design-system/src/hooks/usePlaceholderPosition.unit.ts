import {renderHook, act} from '@testing-library/react-hooks';
import {usePlaceholderPosition} from './usePlaceholderPosition';

test('it return the placeholder position related to the drop position', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2));

  const [placeholderPosition, dragEnter, dragLeave] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter(1);
  });
  expect(result.current[0]).toBe('bottom');

  void act(() => {
    dragEnter(3);
  });

  expect(result.current[0]).toBe('top');

  void act(() => {
    dragLeave();
  });

  expect(result.current[0]).toBe('top');

  void act(() => {
    dragLeave();
  });

  expect(result.current[0]).toBe('none');
});

test('it does not give placeholder when dragged element is the same than the dropped', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2));

  const [placeholderPosition, dragEnter] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter(2);
  });

  expect(result.current[0]).toBe('top');
});
