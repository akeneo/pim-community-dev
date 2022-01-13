import {renderHook} from '@testing-library/react-hooks';
import {useAttributeTypes} from './useAttributeTypes';
import {FakeConfigProvider} from '../../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useAttributeTypes());

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('it returns the related attribute types', () => {
  const {result} = renderHook(() => useAttributeTypes(), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual([
    {
      icon: 'bundles/pimui/images/attribute/icon-text.svg',
      identifier: 'text',
      label: 'pim_asset_manager.attribute.type.text',
    },
    {
      icon: 'bundles/pimui/images/attribute/icon-mediafile.svg',
      identifier: 'media_file',
      label: 'pim_asset_manager.attribute.type.media_file',
    },
  ]);
});
