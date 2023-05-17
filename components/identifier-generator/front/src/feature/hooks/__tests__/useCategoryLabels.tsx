import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {useCategoryLabels} from '../useCategoryLabels';

describe('useCategoryLabels', () => {
  test('it get labels or null if not found', async () => {
    const categoryCodes = ['categoryCode1', 'categoryCode2', 'categoryCode3'];
    const {result, waitFor} = renderHook(() => useCategoryLabels(categoryCodes), {wrapper: createWrapper()});
    await waitFor(() => Object.keys(result.current).length > 0);

    expect(result.current).toEqual({
      categoryCode1: 'Category code 1',
      categoryCode2: 'Category code 2',
      categoryCode3: null,
    });
  });
});
