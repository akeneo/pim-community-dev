import {useDocumentVisibility} from '../../../../src/hooks/useDocumentVisibility';
import {renderHookWithProviders} from '../utils';

test('It return the visibility of the document', () => {
  const {result} = renderHookWithProviders(() => useDocumentVisibility());

  expect(result.current).toEqual(true);
});

test('It return false when the document is not visible', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useDocumentVisibility());

  Object.defineProperty(document, 'visibilityState', {value: 'hidden'});
  document.dispatchEvent(new Event('visibilitychange'));

  await waitForNextUpdate();

  expect(result.current).toEqual(false);
});
