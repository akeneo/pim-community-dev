import {renderHook} from '@testing-library/react-hooks';
import {useDrop} from './useDrop';

test('it provide functions for drag and drop', () => {
  const {result} = renderHook(() => useDrop('122', 10, jest.fn));

  const [handleDrop, handleDragOver] = result.current;

  expect(handleDrop).not.toBeNull();
  expect(handleDragOver).not.toBeNull();
});

test('it does not call on reorder callback when user try to drag and drop to another table', () => {
  const onReorderHandler = jest.fn();
  const {result} = renderHook(() => useDrop('122', 10, onReorderHandler));

  const [handleDrop] = result.current;
  handleDrop({
    dataTransfer: {
      getData(): string {
        return '1';
      }
    },
    currentTarget: {
      dataset: {
        tableId: '123'
      }
    },
    target: {
      dataset: {},
      parentElement: {
        dataset: {
          draggableIndex: '2'
        } as any
      } as any,
    },
  } as any);

  expect(onReorderHandler).not.toHaveBeenCalled();
});

test('it does not on reorder callback when user drag and drop', () => {
  const onReorderHandler = jest.fn();
  const stopPropagation = jest.fn();
  const {result} = renderHook(() => useDrop('122', 4, onReorderHandler));

  const [handleDrop] = result.current;
  handleDrop({
    dataTransfer: {
      getData(): string {
        return '1';
      }
    },
    currentTarget: {
      dataset: {
        tableId: '122'
      }
    },
    target: {
      dataset: {},
      parentElement: {
        dataset: {
          draggableIndex: '2'
        } as any
      } as any,
    },
    stopPropagation: stopPropagation
  } as any);

  expect(onReorderHandler).toHaveBeenCalledWith([0, 2, 1, 3]);
  expect(stopPropagation).toHaveBeenCalled();
});

test('it throw an error when we cannot find the target index', () => {
  const onReorderHandler = jest.fn();
  const {result} = renderHook(() => useDrop('122', 4, onReorderHandler));

  const [handleDrop] = result.current;

  expect(() => handleDrop({
    dataTransfer: {
      getData(): string {
        return '1';
      }
    },
    currentTarget: {
      dataset: {
        tableId: '122'
      }
    },
    target: {
      dataset: {},
      parentElement: undefined,
    },
    stopPropagation: jest.fn
  } as any)).toThrow();
});

test('it does not call on reorder callback on dragover', () => {
  const onReorderHandler = jest.fn();
  const stopPropagation = jest.fn();
  const preventDefault = jest.fn();
  const {result} = renderHook(() => useDrop('122', 4, onReorderHandler));

  const [, handleDragOver] = result.current;
  handleDragOver({
    stopPropagation,
    preventDefault
  } as any);

  expect(stopPropagation).toHaveBeenCalled();
  expect(preventDefault).toHaveBeenCalled();
  expect(onReorderHandler).not.toBeCalled();
});
