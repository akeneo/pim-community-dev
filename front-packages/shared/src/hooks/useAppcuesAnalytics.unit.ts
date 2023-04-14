import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useAppcuesAnalytics} from './useAppcuesAnalytics';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAppcuesAnalytics());

  expect(() => result.current).toThrowError('[DependenciesContext]: Appcues Analytics has not been properly initiated');
});

test('it returns Appcues Analytics', () => {
  const {result} = renderHookWithProviders(() => useAppcuesAnalytics());

  expect(result.current).not.toBeNull();
});
