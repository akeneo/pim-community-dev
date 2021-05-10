import {renderHook, act} from '@testing-library/react-hooks';
import {usePlaceholderPosition} from './usePlaceholderPosition';

test('it return the placeholder position related to the drop position', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2));

  const [placeholderPosition, dragEnter, dragLeave, dragEnd] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter({
      dataTransfer: {
        getData: () => 1
      }
    } as any);
  });
  expect(result.current[0]).toBe('bottom');

  void act(() => {
    dragEnter({
      dataTransfer: {
        getData: () => 3
      }
    } as any);
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

  void act(() => {
    dragEnd();
  });
  expect(result.current[0]).toBe('none');
});

test('it do not give placeholder when dragged element is the same than the dropped', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2));

  const [placeholderPosition, dragEnter] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter({
      dataTransfer: {
        getData: () => 2
      }
    } as any);
  });
  expect(result.current[0]).toBe('none');
});
