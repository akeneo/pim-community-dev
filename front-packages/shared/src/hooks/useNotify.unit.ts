import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useNotify} from './useNotify';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useNotify());

  expect(() => result.current).toThrowError('[DependenciesContext]: Notify has not been properly initiated');
});

test('it returns the Notify', () => {
  const {result} = renderHookWithProviders(() => useNotify());

  expect(result.current).not.toBeNull();
});
