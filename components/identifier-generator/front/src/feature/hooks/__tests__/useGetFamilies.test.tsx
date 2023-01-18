import {renderHook, act} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useGetFamilies, usePaginatedFamilies} from '../useGetFamilies';

describe('usePaginatedFamilies', () => {
  test('it paginates families', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Family${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_families', 'GET', {ok: true, json: page1});
    const {result, waitFor} = renderHook(() => usePaginatedFamilies(), {wrapper: createWrapper()});
    await waitFor(() => !!result.current.families);
    expectCall();
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(page1);

    const page2 = [...Array(10)].map((_, i) => ({code: `Family${i + 20}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_families', 'GET', {ok: true, json: page2});
    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => result.current.families && result.current.families.length > 20);
    expectCall2();
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual([...page1, ...page2]);
  });

  test('it searches families', async () => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Family${i}`, labels: {}}));

    const expectCall = mockResponse('akeneo_identifier_generator_get_families', 'GET', {ok: true, json: page1});
    const {result, waitFor} = renderHook(() => usePaginatedFamilies(), {wrapper: createWrapper()});
    await waitFor(() => !!result.current.families);
    expectCall();
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(page1);

    const pageSearch = [...Array(3)].map((_, i) => ({code: `Family${i * 2}`, labels: {}}));
    const expectCall2 = mockResponse('akeneo_identifier_generator_get_families', 'GET', {
      ok: true,
      json: pageSearch,
    });
    act(() => {
      result.current.handleSearchChange('yolo');
    });
    await waitFor(() => result.current.families && result.current.families.length === 3);
    expectCall2();
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(pageSearch);
  });

  test('it returns nothing on initialization', async () => {
    const {result, waitFor} = renderHook(() => useGetFamilies({page: 1, search: '', codes: []}), {
      wrapper: createWrapper(),
    });
    await waitFor(() => !!result.current.data);
    expect(result.current.data).toEqual([]);
  });
});
