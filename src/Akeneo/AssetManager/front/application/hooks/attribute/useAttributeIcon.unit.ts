import {renderHook} from '@testing-library/react-hooks';
import {useAttributeIcon} from './useAttributeIcon';
import {FakeConfigProvider} from '../../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAttributeIcon('text'));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It throw when attribute type is not found', () => {
  const {result} = renderHook(() => useAttributeIcon('non_existing_type'), {wrapper: FakeConfigProvider});

  expect(() => result.current).toThrowError();
});

test('it returns the related attribute icon', () => {
  const {result} = renderHook(() => useAttributeIcon('text'), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual('bundles/pimui/images/attribute/icon-text.svg');
});
