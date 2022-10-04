import {waitFor} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {useIdentifierAttributes} from '../useIdentifierAttributes';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from 'react-dom/test-utils';
import {FlattenAttribute} from '../../models/attributes';

describe('useIdentifierAttributes', () => {
  beforeEach(() => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([{code: 'sku', label: 'Sku'}]),
    } as Response);
  });

  test('it retrieves identifier attribute list', async () => {
    const {result} = renderHook<null, {isSuccess: boolean; data: FlattenAttribute[] | undefined}>(
      () => useIdentifierAttributes(),
      {
        wrapper: createWrapper(),
      }
    );

    await waitFor(() => result.current.isSuccess);

    act(() => {
      expect(result.current.data).toBeDefined();
      expect(result.current.data).toEqual([{code: 'sku', label: 'Sku'}]);
    });
  });
});
