'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useUnsavedChanges} from 'akeneomeasure/shared/hooks/use-unsaved-changes';
import {renderHook, act} from '@testing-library/react-hooks';

test('It can define if the model has been changed', async () => {
  const {result} = renderHook(() => useUnsavedChanges('nice_message'));
  act(() => result.current[1]('nice'));
  expect(result.current[0]).toEqual(false);

  act(() => result.current[1]('nicee'));
  expect(result.current[0]).toEqual(true);
});

test('It can work with unloaded model', async () => {
  const {result} = renderHook(() => useUnsavedChanges('nice_message'));
  act(() => result.current[1](null));
  expect(result.current[0]).toEqual(false);
});

test('It can reset the unsaved changes', async () => {
  const {result} = renderHook(() => useUnsavedChanges('nice_message'));

  // We initialize it with "nice"
  act(() => result.current[1]('nice'));
  expect(result.current[0]).toEqual(false);

  // We add an "e" to our original value
  act(() => result.current[1]('nicee'));
  expect(result.current[0]).toEqual(true);

  // We "save" so the model is reset
  act(() => result.current[2]('nicee'));
  expect(result.current[0]).toEqual(false);

  // We go back to the original value
  act(() => result.current[1]('nice'));
  expect(result.current[0]).toEqual(true);
});
