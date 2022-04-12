import {renderHook, act} from '@testing-library/react-hooks';
import {useFilteredContributors} from './useFilteredContributors';

const contributors = {
    1: 'contributor1@example.com',
    2: 'contributor2@example.com',
    3: 'another@example.com',
};

test('it returns all contributors by default', () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    expect(result.current.filteredContributors).toBe(contributors);
});

test('we can search contributors by email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    await act(async () => result.current.search('contributor'));
    expect(result.current.filteredContributors).toEqual({
        1: 'contributor1@example.com',
        2: 'contributor2@example.com',
    });
});

test('it returns no result if the search does not match any email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    await act(async () => result.current.search('test'));
    expect(result.current.filteredContributors).toEqual({});
});
