import {renderHookWithProviders} from '../tests/utils';
import {useRoute} from './useRoute';

test('it returns the Route', () => {
  const {result} = renderHookWithProviders(() => useRoute('https://akeneo.com/api'));

  expect(result.current).toEqual('https://akeneo.com/api');
});
