import {renderHook} from '@testing-library/react-hooks';
import {useSaveGenerator} from '../useSaveGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from '@testing-library/react';
import initialGenerator from '../../tests/fixtures/initialGenerator';

describe('useSaveGenerator', () => {
  it('should save a generator without failure', async () => {
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() => {
      return !!result?.current?.save;
    });

    act(() => {
      result?.current?.save(initialGenerator);
    });

    await waitFor(() => {
      return result.current.isSuccess;
    });

    expect(result.current.isSuccess).toBe(true);
  });

  it('should handle errors on save', async () => {
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() =>  !!result?.current?.save);

    act(() => {
      result?.current?.save({...initialGenerator, code: ' wrong,code'});
    });

    await waitFor(() => result.current.error.length > 0);
    expect(result.current.error).toEqual([
      {
        message: 'Association type code may contain only letters, numbers and underscores',
        path: 'code',
      },
    ]);
  });
});
