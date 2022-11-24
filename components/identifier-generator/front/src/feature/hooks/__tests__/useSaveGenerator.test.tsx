import {renderHook} from '@testing-library/react-hooks';
import {useSaveGenerator} from '../useSaveGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from '@testing-library/react';
import {mockResponse} from '../../tests/test-utils';

const generator = {
  code: 'code',
  target: 'sku',
  structure: [],
  delimiter: '-',
  labels: {en_US: 'My Generator'},
  conditions: [],
};

describe('useSaveGenerator', () => {
  it('should save a generator without failure', async () => {
    mockResponse('akeneo_identifier_generator_rest_update', 'PATCH', {
      ok: true,
      json: () => Promise.resolve(generator),
    });

    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() => {
      return !!result?.current?.save;
    });

    act(() => {
      result?.current?.save(generator);
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });
  });

  it('should handle errors on save', async () => {
    mockResponse('akeneo_identifier_generator_rest_update', 'PATCH', {
      ok: false,
      json: [
        {
          message: 'Association type code may contain only letters, numbers and underscores',
          path: 'code',
        },
      ],
    });
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() => {
      return !!result?.current?.save;
    });

    act(() => {
      result?.current?.save(generator);
    });
    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(result.current.error).toEqual([
      {
        message: 'Association type code may contain only letters, numbers and underscores',
        path: 'code',
      },
    ]);
  });
});
