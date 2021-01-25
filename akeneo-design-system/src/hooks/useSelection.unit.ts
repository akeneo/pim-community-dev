import {renderHook, act} from '@testing-library/react-hooks';
import {useSelection} from './useSelection';

test('It can generate a basic selection', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [collection, selectionState, isItemSelected, onSelectionChange, , selectedCount] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('donald')).toBe(false);
  expect(isItemSelected('melania')).toBe(false);
  expect(selectedCount).toEqual(0);
  expect(collection).toEqual([]);

  void act(() => {
    onSelectionChange('donald', true);
  });

  const [halfCollection, halfSelectionState, isHalfItemSelected, , , halfSelectedCount] = result.current;

  expect(isHalfItemSelected('donald')).toBe(true);
  expect(isHalfItemSelected('melania')).toBe(false);
  expect(halfSelectionState).toBe('mixed');
  expect(halfSelectedCount).toEqual(1);
  expect(halfCollection).toEqual(['donald']);

  void act(() => {
    onSelectionChange('melania', true);
  });

  const [
    completeCollection,
    completeSelectionState,
    isCompleteItemSelected,
    ,
    ,
    completeSelectedCount,
  ] = result.current;

  expect(isCompleteItemSelected('donald')).toBe(true);
  expect(isCompleteItemSelected('melania')).toBe(true);
  expect(completeSelectionState).toBe(true);
  expect(completeSelectedCount).toEqual(2);
  expect(completeCollection).toEqual(['donald', 'melania']);
});

test('It can handle unselection all', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);

  void act(() => {
    onSelectionChange('nice', true);
    onSelectAllChange(false);
  });

  const [, emptySelectionState, isEmptyItemSelected] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(false);
  expect(emptySelectionState).toBe(false);
});

test('It can handle selection all', () => {
  const {result} = renderHook(() => useSelection<string>(3));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);
  expect(selectedCount).toEqual(0);

  void act(() => {
    onSelectionChange('nice', true);
    onSelectAllChange(true);
  });

  const [emptyCollection, emptySelectionState, isEmptyItemSelected, , , emptySelectedCount] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(true);
  expect(isEmptyItemSelected('donald')).toBe(true);
  expect(isEmptyItemSelected('melania')).toBe(true);
  expect(emptySelectionState).toBe(true);
  expect(emptySelectedCount).toBe(3);
  expect(emptyCollection).toEqual([]);

  void act(() => {
    onSelectionChange('donald', false);
  });

  const [
    unselectedCollection,
    unselectedSelectionState,
    isUnselectedItemSelected,
    ,
    ,
    unselectedCount,
  ] = result.current;
  expect(isUnselectedItemSelected('donald')).toBe(false);
  expect(unselectedSelectionState).toBe('mixed');
  expect(unselectedCollection).toEqual(['donald']);
  expect(unselectedCount).toEqual(2);
});

test('It can handle selection after selection all', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);
  expect(selectedCount).toEqual(0);

  void act(() => {
    onSelectAllChange(true);
    onSelectionChange('nice', false);
    onSelectionChange('nice', true);
  });

  const [, emptySelectionState, isEmptyItemSelected, , , emptySelectedCount] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(true);
  expect(isEmptyItemSelected('donald')).toBe(true);
  expect(emptySelectionState).toBe(true);
  expect(emptySelectedCount).toEqual(2);
});

test('It can handle unselection after unselect all', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);
  expect(selectedCount).toEqual(0);

  void act(() => {
    onSelectAllChange(false);
    onSelectionChange('nice', true);
    onSelectionChange('nice', false);
  });

  const [, emptySelectionState, isEmptyItemSelected, , , emptySelectedCount] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(false);
  expect(isEmptyItemSelected('donald')).toBe(false);
  expect(emptySelectionState).toBe(false);
  expect(emptySelectedCount).toEqual(0);
});
