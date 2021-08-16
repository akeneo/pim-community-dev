import {renderHook, act} from '@testing-library/react-hooks';
import {usePlaceholderPosition} from './usePlaceholderPosition';

test('it return the placeholder position related to the drop position', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2, 1));

  const [placeholderPosition, dragEnter] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter();
  });
  expect(result.current[0]).toBe('bottom');

  const {result: reRenderedResult} = renderHook(() => usePlaceholderPosition(2, 3));
  const [, reRenderedDragEnter, dragLeave, dragEnd] = reRenderedResult.current;

  void act(() => {
    reRenderedDragEnter();
  });

  expect(reRenderedResult.current[0]).toBe('top');

  void act(() => {
    dragLeave();
  });
  expect(reRenderedResult.current[0]).toBe('none');

  void act(() => {
    dragEnd();
  });
  expect(reRenderedResult.current[0]).toBe('none');
});

test('it does not give placeholder when dragged element is the same than the dropped', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2, 2));

  const [placeholderPosition, dragEnter] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter();
  });

  expect(result.current[0]).toBe('top');
});

test('it does nothing if dragged element is empty', () => {
  const {result} = renderHook(() => usePlaceholderPosition(2, null));

  const [placeholderPosition, dragEnter, dragLeave] = result.current;
  expect(placeholderPosition).toBe('none');

  void act(() => {
    dragEnter();
    dragLeave();
  });

  expect(result.current[0]).toBe('none');
});
