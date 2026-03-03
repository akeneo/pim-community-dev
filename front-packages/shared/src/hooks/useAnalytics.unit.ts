import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useAnalytics} from './useAnalytics';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAnalytics());

  expect(() => result.current).toThrowError('[DependenciesContext]: Analytics has not been properly initiated');
});

test('it returns Analytics', () => {
  const {result} = renderHookWithProviders(() => useAnalytics());

  expect(result.current).not.toBeNull();
});
