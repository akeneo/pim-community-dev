import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {useTabView} from './useTabView';
import {default as EditTabView} from 'akeneoassetmanager/application/component/asset-family/edit/attribute';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useTabView('akeneo_asset_manager_asset_family_edit', 'attribute'));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It throw when sidebar identifier is not found', () => {
  const {result} = renderHook(() => useTabView('non_existent_sidebar_identifier', 'attribute'), {
    wrapper: FakeConfigProvider,
  });

  expect(() => result.current).toThrowError();
});

test('It throw when tab code is not found', () => {
  const {result} = renderHook(() => useTabView('akeneo_asset_manager_asset_family_edit', 'non_existent_tab_code'), {
    wrapper: FakeConfigProvider,
  });

  expect(() => result.current).toThrowError();
});

test('It returns the related tab view', () => {
  const {result} = renderHook(() => useTabView('akeneo_asset_manager_asset_family_edit', 'attribute'), {
    wrapper: FakeConfigProvider,
  });

  expect(result.current).toEqual(EditTabView);
});
