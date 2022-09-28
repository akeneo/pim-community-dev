import {waitFor} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {useIdentifierAttributes} from '../useIdentifierAttributes';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from 'react-dom/test-utils';

describe('useIdentifierAttributes', () => {
  beforeEach(() => {
    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([{code: 'sku', label: 'Sku'}]),
    });
  });

  test('it retrieves identifier attribute list', async () => {
    const {result} = renderHook(() => useIdentifierAttributes(), {
      wrapper: createWrapper(),
    });

    // @ts-ignore
    await waitFor(() => result.current.isSuccess);

    act(() => {
      expect(result.current.data).toBeDefined();
      expect(result.current.data).toEqual([{code: 'sku', label: 'Sku'}]);
    });
  });
});
