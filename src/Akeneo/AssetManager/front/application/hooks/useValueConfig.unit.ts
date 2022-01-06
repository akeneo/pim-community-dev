import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider, fakeConfig} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {useValueConfig} from './useValueConfig';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useValueConfig());

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It returns the value config', () => {
  const {result} = renderHook(() => useValueConfig(), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(fakeConfig.value);
});
