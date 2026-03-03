import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useFeatureFlags} from './useFeatureFlags';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useFeatureFlags());

  expect(() => result.current).toThrowError('[DependenciesContext]: FeatureFlags has not been properly initiated');
});

test('it returns the FeatureFlags', () => {
  const {result} = renderHookWithProviders(() => useFeatureFlags());

  expect(result.current).not.toBeNull();
});
