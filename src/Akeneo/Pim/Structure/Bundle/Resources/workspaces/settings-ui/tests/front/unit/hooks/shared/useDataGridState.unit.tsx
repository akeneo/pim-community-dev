import {act, renderHook} from '@testing-library/react-hooks';
import {useDataGridState, useInitialDataGridState} from '@akeneo-pim-community/settings-ui/src/hooks/shared';
import {
  AfterMoveRowHandler,
  CompareRowDataHandler,
} from '@akeneo-pim-community/settings-ui/src/components/shared/providers';
import {aDragEvent, aListOfData} from '../../../utils/provideDataGridHelper';

describe('useInitialDataGridState', () => {
  const renderUseInitialDataGridState = (
    isDraggable: boolean,
    dataSource: any[],
    handleAfterMove: AfterMoveRowHandler<any>,
    compareRowData: CompareRowDataHandler<any>,
    isFilterable: boolean,
    isReorderActive: boolean
  ) => {
    return renderHook(() =>
      useInitialDataGridState(isDraggable, dataSource, handleAfterMove, compareRowData, isFilterable, isReorderActive)
    );
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns the state of the datagrid', () => {
    const {result} = renderUseInitialDataGridState(
      false,
      [],
      () => {},
      () => -1,
      false,
      false
    );

    expect(result.current.draggedData).toBeNull();
    expect(result.current.draggedIndex).toBe(-1);
    // @ts-ignore
    expect(result.current.isDraggable).toBeFalsy();
    expect(result.current.dataSource).toEqual([]);
    expect(result.current.isDragged).toBeDefined();
    expect(result.current.moveUp).toBeDefined();
    expect(result.current.moveDown).toBeDefined();
    expect(result.current.moveStart).toBeDefined();
    expect(result.current.moveOver).toBeDefined();
    expect(result.current.moveDrop).toBeDefined();
    expect(result.current.moveEnd).toBeDefined();
  });

  test('it checks current dragged item', () => {
    const dataSource = aListOfData();
    const {result} = renderUseInitialDataGridState(
      true,
      dataSource,
      () => {},
      (itemA: any, itemB: any) => (Object.is(itemA, itemB) ? 0 : -1),
      false,
      false
    );
    const element = document.createElement('div');
    const dragStartEvent = aDragEvent('dragstart', element);
    const dropEvent = aDragEvent('drop', element);
    const dragEndEvent = aDragEvent('dragend', element);
    const afterDropRowHandler = jest.fn();

    expect(result.current.isDragged(dataSource[1])).toBeFalsy();
    expect(result.current.draggedData).toBeNull();
    expect(result.current.draggedIndex).toBe(-1);

    act(() => {
      result.current.moveStart(dragStartEvent as any, dataSource[1], 1, null);
    });

    expect(result.current.isDragged(dataSource[1])).toBeTruthy();
    expect(result.current.draggedData).toBe(dataSource[1]);
    expect(result.current.draggedIndex).toBe(1);

    act(() => {
      result.current.moveDrop(dropEvent as any, afterDropRowHandler);
    });

    expect(afterDropRowHandler).toBeCalled();
    expect(result.current.isDragged(dataSource[1])).toBeFalsy();
    expect(result.current.draggedData).toBeNull();
    expect(result.current.draggedIndex).toBe(-1);

    act(() => {
      result.current.moveStart(dragStartEvent as any, dataSource[1], 1, null);
    });

    act(() => {
      result.current.moveEnd(dragEndEvent as any, afterDropRowHandler);
    });

    expect(result.current.isDragged(dataSource[1])).toBeFalsy();
    expect(result.current.draggedData).toBeNull();
    expect(result.current.draggedIndex).toBe(-1);
    expect(afterDropRowHandler).toBeCalled();
  });

  test('it moves item up', () => {
    const dataSource = aListOfData();
    const afterMoveHandler = jest.fn();
    const {result} = renderUseInitialDataGridState(
      true,
      dataSource,
      afterMoveHandler,
      (itemA: any, itemB: any) => (Object.is(itemA, itemB) ? 0 : -1),
      false,
      false
    );

    act(() => {
      result.current.moveUp(dataSource[2], dataSource[1]);
    });

    expect(result.current.draggedIndex).toBe(1);
    expect(afterMoveHandler).toBeCalledWith([dataSource[0], dataSource[2], dataSource[1], dataSource[3]]);
  });

  test('it moves item down', () => {
    const dataSource = aListOfData();
    const afterMoveHandler = jest.fn();
    const {result} = renderUseInitialDataGridState(
      true,
      dataSource,
      afterMoveHandler,
      (itemA: any, itemB: any) => (Object.is(itemA, itemB) ? 0 : -1),
      false,
      false
    );

    act(() => {
      result.current.moveDown(dataSource[2], dataSource[3]);
    });

    expect(result.current.draggedIndex).toBe(3);
    expect(afterMoveHandler).toBeCalledWith([dataSource[0], dataSource[1], dataSource[3], dataSource[2]]);
  });
});

describe('useDataGridState', () => {
  const renderUseDataGridStateWithoutProvider = () => {
    return renderHook(() => useDataGridState());
  };

  test('it throws an error if it used outside context', () => {
    const {result} = renderUseDataGridStateWithoutProvider();
    expect(result.error).not.toBeNull();
  });
});
