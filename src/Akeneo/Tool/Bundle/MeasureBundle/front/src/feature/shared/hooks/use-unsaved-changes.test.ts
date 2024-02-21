import {useUnsavedChanges} from './use-unsaved-changes';
import {renderHook, act} from '@testing-library/react-hooks';

test('It can define if the model has been changed', async () => {
  let entity = 'nice';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'nice_message'));

  let [isModified] = result.current;
  expect(isModified).toEqual(false);

  entity = 'niceee';
  rerender();

  [isModified] = result.current;
  expect(isModified).toEqual(true);
});

test('It can work with unloaded model', async () => {
  const {result} = renderHook(() => useUnsavedChanges(null, 'nice_message'));

  let [isModified] = result.current;
  expect(isModified).toEqual(false);
});

test('It can reset the unsaved changes', async () => {
  let entity = 'nice';
  let isModified: boolean, resetState: () => void;
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'nice_message'));

  // We initialize it with "nice"
  [isModified] = result.current;
  expect(isModified).toEqual(false);

  // We add an "e" to our original value
  entity = 'niceee';
  rerender();
  [isModified, resetState] = result.current;
  expect(isModified).toEqual(true);

  // We "save" so the model is reset
  act(() => resetState());
  rerender();
  [isModified, resetState] = result.current;
  expect(isModified).toEqual(false);

  // We go back to the original value
  entity = 'nice';
  rerender();
  [isModified, resetState] = result.current;
  expect(isModified).toEqual(true);
});
