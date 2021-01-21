import {renderHook, act} from '@testing-library/react-hooks';
import {useSelection} from './useSelection';

test('It can generate a basic selection', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('donald')).toBe(false);
  expect(isItemSelected('melania')).toBe(false);

  void act(() => {
    onSelectionChange('donald', true);
  });

  const [, halfSelectionState, isHalfItemSelected] = result.current;

  expect(isHalfItemSelected('donald')).toBe(true);
  expect(isHalfItemSelected('melania')).toBe(false);
  expect(halfSelectionState).toBe('mixed');

  void act(() => {
    onSelectionChange('melania', true);
  });

  const [, completeSelectionState, isCompleteItemSelected] = result.current;

  expect(isCompleteItemSelected('donald')).toBe(true);
  expect(isCompleteItemSelected('melania')).toBe(true);
  expect(completeSelectionState).toBe(true);
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
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);

  void act(() => {
    onSelectionChange('nice', true);
    onSelectAllChange(true);
  });

  const [, emptySelectionState, isEmptyItemSelected] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(true);
  expect(isEmptyItemSelected('donald')).toBe(true);
  expect(isEmptyItemSelected('melania')).toBe(true);
  expect(emptySelectionState).toBe(true);

  void act(() => {
    onSelectionChange('donald', false);
  });

  const [, unselectedSelectionState, isUnselectedItemSelected] = result.current;
  expect(isUnselectedItemSelected('donald')).toBe(false);
  expect(unselectedSelectionState).toBe('mixed');
});

test('It can handle selection after selection all', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);

  void act(() => {
    onSelectAllChange(true);
    onSelectionChange('nice', false);
    onSelectionChange('nice', true);
  });

  const [, emptySelectionState, isEmptyItemSelected] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(true);
  expect(isEmptyItemSelected('donald')).toBe(true);
  expect(emptySelectionState).toBe(true);
});

test('It can handle unselection after unselect all', () => {
  const {result} = renderHook(() => useSelection<string>(2));

  const [, selectionState, isItemSelected, onSelectionChange, onSelectAllChange] = result.current;

  expect(selectionState).toBe(false);
  expect(isItemSelected('nice')).toBe(false);

  void act(() => {
    onSelectAllChange(false);
    onSelectionChange('nice', true);
    onSelectionChange('nice', false);
  });

  const [, emptySelectionState, isEmptyItemSelected] = result.current;

  expect(isEmptyItemSelected('nice')).toBe(false);
  expect(isEmptyItemSelected('donald')).toBe(false);
  expect(emptySelectionState).toBe(false);
});
