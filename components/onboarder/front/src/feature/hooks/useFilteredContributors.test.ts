import {renderHook} from '@testing-library/react-hooks';
import {useFilteredContributors} from './useFilteredContributors';

const contributors = ['contributor1@example.com', 'contributor2@example.com', 'another@example.com'];

test('it returns all contributors by default', () => {
    const {result} = renderHook(() => useFilteredContributors(contributors, ''));
    expect(result.current).toStrictEqual(contributors);
});

test('we can search contributors by email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors, 'contributor'));
    expect(result.current).toStrictEqual(['contributor1@example.com', 'contributor2@example.com']);
});

test('it returns no result if the search does not match any email', async () => {
    const {result} = renderHook(() => useFilteredContributors(contributors, 'test'));
    expect(result.current).toStrictEqual([]);
});
