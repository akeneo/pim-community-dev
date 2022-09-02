import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider, fakeConfig} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';
import {useSidebarConfig} from './useSidebarConfig';

test('it throws when the provider is not found', () => {
  const {result} = renderHook(() => useSidebarConfig());

  expect(() => result.current).toThrowError('ConfigContext has not been properly initiated');
});

test('It returns the sidebar config', () => {
  const {result} = renderHook(() => useSidebarConfig(), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(fakeConfig.sidebar);
});
