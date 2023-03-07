import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useCategoryLabels} from '../useCategoryLabels';

describe('useCategoryLabels', () => {
  test('it get labels or null if not found', async () => {
    const categoryCodes = ['categoryCode1', 'categoryCode2', 'categoryCode3'];
    const labels = {
      categoryCode1: 'Category code 1',
      categoryCode2: 'Category code 2',
    };
    const expectCall = mockResponse('akeneo_identifier_generator_get_category_labels', 'GET', {ok: true, json: labels});
    const {result, waitFor} = renderHook(() => useCategoryLabels(categoryCodes), {wrapper: createWrapper()});
    await waitFor(() => Object.keys(result.current).length > 0);

    expectCall();
    expect(result.current).toEqual({
      categoryCode1: 'Category code 1',
      categoryCode2: 'Category code 2',
      categoryCode3: null,
    });
  });
});
