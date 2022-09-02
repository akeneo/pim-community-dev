import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider, fakeConfig} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {useAttributeConfig} from './useAttributeConfig';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAttributeConfig());

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It returns the attribute config', () => {
  const {result} = renderHook(() => useAttributeConfig(), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(fakeConfig.attribute);
});
