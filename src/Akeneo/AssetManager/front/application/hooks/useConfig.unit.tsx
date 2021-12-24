import {renderHook} from '@testing-library/react-hooks';
import {useConfig} from './useConfig';
import {FakeConfigProvider, fakeConfig} from '../../../tests/front/unit/akeneoassetmanager/utils/FakeConfigProvider';

test('It returns the config', () => {
  const {result} = renderHook(() => useConfig(), {wrapper: FakeConfigProvider});

  expect(result.current).toEqual(fakeConfig);
});
