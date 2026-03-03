import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useUserContext} from './useUserContext';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useUserContext());

  expect(() => result.current).toThrowError('[DependenciesContext]: UserContext has not been properly initiated');
});

test('it returns the UserContext', () => {
  const {result} = renderHookWithProviders(() => useUserContext());

  expect(result.current).not.toBeNull();
});
