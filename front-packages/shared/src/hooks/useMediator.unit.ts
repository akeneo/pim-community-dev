import {renderHook} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../tests/utils';
import {useMediator} from './useMediator';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useMediator());

  expect(() => result.current).toThrowError('[DependenciesContext]: Mediator has not been properly initiated');
});

test('it returns the Mediator', () => {
  const {result} = renderHookWithProviders(() => useMediator());

  expect(result.current).not.toBeNull();
});
