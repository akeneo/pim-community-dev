import {act, renderHook} from '@testing-library/react-hooks';
import {usePaginatedOptions} from '../useGetSelectOptions';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {firstPaginatedResponse, secondPaginatedResponse} from '../../tests/fixtures/options';

describe('useGetSelectOptions', () => {
  test('it paginates select options', async () => {
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
      await waitFor(() => result.current?.options?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });

    expect(hookResult?.current?.options).toBeDefined();
    expect(hookResult?.current?.options).toEqual(firstPaginatedResponse);

    act(() => {
      hookResult.current.handleNextPage();
    });
    await hookWaitFor(() => hookResult.current.options && hookResult.current.options.length > 20);
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual([...firstPaginatedResponse, ...secondPaginatedResponse]);
  });

  test('it searches options', async () => {
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
      await waitFor(() => !result.current.isLoading);
      hookResult = result;
      hookWaitFor = waitFor;
    });

    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options.length).toBeGreaterThan(0);
    expect(hookResult.current.options).toEqual(firstPaginatedResponse);

    act(() => {
      hookResult.current.handleSearchChange('OptionF');
    });
    await hookWaitFor(() => hookResult.current.options && hookResult.current.options.length === 1);
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual([{code: 'option_f', labels: {en_US: 'OptionF'}}]);
  });
});
