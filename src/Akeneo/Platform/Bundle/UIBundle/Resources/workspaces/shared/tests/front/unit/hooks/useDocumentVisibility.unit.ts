import {act} from '@testing-library/react-hooks';
import {useDocumentVisibility} from '../../../../src/hooks/useDocumentVisibility';
import {renderHookWithProviders} from '../utils';

test('It returns the visibility of the document', () => {
  const {result} = renderHookWithProviders(() => useDocumentVisibility());

  expect(result.current).toEqual(true);
});

test('It returns false when the document is hidden', () => {
  const {result} = renderHookWithProviders(() => useDocumentVisibility());

  act(() => {
    Object.defineProperty(document, 'visibilityState', {value: 'hidden'});
    window.dispatchEvent(new Event('visibilitychange'));
  });

  expect(result.current).toEqual(false);
});
