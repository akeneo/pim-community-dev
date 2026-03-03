import {act, renderHook} from '@testing-library/react-hooks';
import {usePaginatedOptions} from '../useGetSelectOptions';
import {mockResponse} from '../../tests/test-utils';
import {createWrapper} from '../../tests/hooks/config/createWrapper';

describe('useGetSelectOptions', () => {
  test('it paginates select options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
      await waitFor(() => result.current?.options?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });

    expectCall();
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual(page1);

    const page2 = [...Array(10)].map((_, i) => ({code: `Option${i + 20}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page2,
    });
    act(() => {
      hookResult.current.handleNextPage();
    });
    await hookWaitFor(() => hookResult.current.options && hookResult.current.options.length > 20);
    expectCall2();
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual([...page1, ...page2]);
  });

  test('it searches options', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: page1,
    });
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => usePaginatedOptions('brand'), {wrapper: createWrapper()});
      await waitFor(() => !result.current.isLoading);
      hookResult = result;
      hookWaitFor = waitFor;
    });

    expectCall();
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual(page1);

    const pageSearch = [...Array(3)].map((_, i) => ({code: `Option${i * 2}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_attribute_options', 'GET', {
      ok: true,
      json: pageSearch,
    });
    act(() => {
      hookResult.current.handleSearchChange('searchedOption');
    });
    await hookWaitFor(() => hookResult.current.options && hookResult.current.options.length === 3);
    expectCall2();
    expect(hookResult.current.options).toBeDefined();
    expect(hookResult.current.options).toEqual(pageSearch);
  });
});
