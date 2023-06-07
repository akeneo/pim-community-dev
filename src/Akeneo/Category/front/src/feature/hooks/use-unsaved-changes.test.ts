import {useUnsavedChanges} from './use-unsaved-changes';
import {renderHook, act} from '@testing-library/react-hooks';

test('It can use hook and update saved state', async () => {
  const {result, rerender} = renderHook(() => useUnsavedChanges('nice_message'));

  let [isSaved, setSaved] = result.current;
  expect(isSaved).toEqual(true);

  setSaved(false);
  rerender();

  [isSaved] = result.current;
  expect(isSaved).toEqual(false);
});
