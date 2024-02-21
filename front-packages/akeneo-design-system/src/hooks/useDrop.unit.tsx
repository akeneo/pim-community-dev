import {renderHook} from '@testing-library/react-hooks';
import {useDrop} from './useDrop';

const onReorder = jest.fn();

test('it does not call on reorder callback when user try to drag and drop to another table', () => {
  const {result} = renderHook(() => useDrop(10, 1, onReorder));

  const [, handleDrop] = result.current;
  const event = {
    currentTarget: {
      dataset: {
        tableId: 'another_table_id',
      },
    },
    target: {
      dataset: {},
      parentElement: {
        dataset: {
          draggableIndex: '2',
        },
      },
    },
    stopPropagation: jest.fn(),
    preventDefault: jest.fn(),
  };

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  handleDrop(event);

  expect(onReorder).not.toHaveBeenCalled();
});

test('it does not on reorder callback when user drag and drop', () => {
  const stopPropagation = jest.fn();
  const preventDefault = jest.fn();
  const {result} = renderHook(() => useDrop(4, 1, onReorder));

  const [tableId, handleDrop] = result.current;
  const event = {
    currentTarget: {
      dataset: {
        tableId,
      },
    },
    target: {
      dataset: {},
      parentElement: {
        dataset: {
          draggableIndex: '2',
        },
      },
    },
    stopPropagation,
    preventDefault,
  };

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  handleDrop(event);

  expect(onReorder).toHaveBeenCalledWith([0, 2, 1, 3]);
  expect(stopPropagation).toHaveBeenCalled();
  expect(preventDefault).toHaveBeenCalled();
});

test('it throws an error when it cannot find the target index', () => {
  const {result} = renderHook(() => useDrop(4, 1, onReorder));

  const [tableId, handleDrop] = result.current;

  expect(() => {
    const event = {
      currentTarget: {
        dataset: {
          tableId,
        },
      },
      target: {
        dataset: {},
        parentElement: null,
      },
      stopPropagation: jest.fn(),
      preventDefault: jest.fn(),
    };

    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    handleDrop(event);
  }).toThrowError('Draggable parent not found');
});

test('it does not call on reorder callback on dragover', () => {
  const stopPropagation = jest.fn();
  const preventDefault = jest.fn();
  const {result} = renderHook(() => useDrop(4, 1, onReorder));

  const [, , handleDragOver] = result.current;
  const event = {
    stopPropagation,
    preventDefault,
  };

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  handleDragOver(event);

  expect(stopPropagation).toHaveBeenCalled();
  expect(preventDefault).toHaveBeenCalled();
  expect(onReorder).not.toBeCalled();
});
