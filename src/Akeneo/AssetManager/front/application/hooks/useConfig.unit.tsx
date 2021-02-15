import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useConfig} from './useConfig';

test('It return the config', () => {
  const {result} = renderHookWithProviders(() => useConfig('value'));

  expect(result.current).toEqual({})
});
