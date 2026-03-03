import {act} from 'react-test-renderer';
import {renderHook} from '@testing-library/react-hooks';
import {useDragElementIndex} from './useDragElementIndex';

test('It manages dragging', () => {
  const {result} = renderHook(() => useDragElementIndex());

  let [draggedElementIndex] = result.current;
  const [, onDrag, onDrop] = result.current;
  expect(draggedElementIndex).toEqual(null);
  void act(() => {
    onDrag(5);
  });
  [draggedElementIndex] = result.current;
  expect(draggedElementIndex).toEqual(5);
  void act(() => {
    onDrop();
  });
  [draggedElementIndex] = result.current;
  expect(draggedElementIndex).toEqual(null);
});
