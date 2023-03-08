import {renderHook, act} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {useGetFamilies, usePaginatedFamilies} from '../useGetFamilies';
import {mockedFamiliesPage1, mockedFamiliesPage2, mockedFamiliesSearch} from '../../tests/fixtures/families';

describe('usePaginatedFamilies', () => {
  test('it paginates families', async () => {
    const {result, waitFor} = renderHook(() => usePaginatedFamilies(), {wrapper: createWrapper()});
    await waitFor(() => !!result.current.families);
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(mockedFamiliesPage1);

    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => result.current.families && result.current.families.length > 20);
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual([...mockedFamiliesPage1, ...mockedFamiliesPage2]);
  });

  test('it searches families', async () => {
    const {result, waitFor} = renderHook(() => usePaginatedFamilies(), {wrapper: createWrapper()});
    await waitFor(() => !!result.current.families);
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(mockedFamiliesPage1);

    act(() => {
      result.current.handleSearchChange('My Family');
    });
    await waitFor(() => result.current.families && result.current.families.length === 3);
    expect(result.current.families).toBeDefined();
    expect(result.current.families).toEqual(mockedFamiliesSearch);
  });

  test('it returns nothing on initialization', async () => {
    const {result, waitFor} = renderHook(() => useGetFamilies({page: 1, search: '', codes: []}), {
      wrapper: createWrapper(),
    });
    await waitFor(() => !!result.current.data);
    expect(result.current.data).toEqual([]);
  });
});
