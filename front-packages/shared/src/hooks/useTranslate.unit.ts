import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useTranslate} from './useTranslate';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useTranslate());

  expect(() => result.current).toThrowError('[DependenciesContext]: Translate has not been properly initiated');
});

test('it returns the Translate', () => {
  const {result} = renderHookWithProviders(() => useTranslate());

  expect(result.current).not.toBeNull();
});
