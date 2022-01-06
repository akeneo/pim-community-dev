import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {useSidebarTabs} from './useSidebarTabs';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useSidebarTabs('akeneo_asset_manager_asset_family_edit'));

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It throw when sidebar identifier is not found', () => {
  const {result} = renderHook(() => useSidebarTabs('non_existent_sidebar_identifier'), {wrapper: FakeConfigProvider});

  expect(() => result.current).toThrowError();
});

test('It returns the sidebar tabs', () => {
  const {result} = renderHook(() => useSidebarTabs('akeneo_asset_manager_asset_family_edit'), {
    wrapper: FakeConfigProvider,
  });

  expect(result.current).toEqual([
    {
      code: 'attribute',
      label: 'First tab',
    },
  ]);
});
