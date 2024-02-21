import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useRouter} from './useRouter';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useRouter());

  expect(() => result.current).toThrowError('[DependenciesContext]: Router has not been properly initiated');
});

test('it returns the Router', () => {
  const {result} = renderHookWithProviders(() => useRouter());

  expect(result.current).not.toBeNull();
});
