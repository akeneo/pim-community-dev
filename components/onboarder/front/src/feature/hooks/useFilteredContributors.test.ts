import {renderHook, act} from '@testing-library/react-hooks';
import {useFilteredContributors} from './useFilteredContributors';

const contributors = [
    {identifier: 'id1', email: 'contributor1@akeneo.com'},
    {identifier: 'id2', email: 'contributor2@akeneo.com'},
    {identifier: 'id3', email: 'another@mycompany.com'},
];

test('it returns all contributors by default', () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    expect(result.current.filteredContributors).toBe(contributors);
});

test('we can search contributors by email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    await act(async () => result.current.search('akeneo'));
    expect(result.current.filteredContributors).toEqual([
        {identifier: 'id1', email: 'contributor1@akeneo.com'},
        {identifier: 'id2', email: 'contributor2@akeneo.com'},
    ]);
});

test('it returns no result if the search does not match any email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors));
    await act(async () => result.current.search('test'));
    expect(result.current.filteredContributors).toEqual([]);
});
