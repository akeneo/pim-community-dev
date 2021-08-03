import {renderHook, act} from '@testing-library/react-hooks';
import {SourceOffset} from 'feature/models';
import {useOffsetAvailableSources} from './useOffsetAvailableSources';

const firstBatch = [
  {
    code: 'system',
    label: 'System',
    children: [
      {
        code: 'category',
        label: 'Categories',
        type: 'property',
      },
      {
        code: 'enabled',
        label: 'Activé',
        type: 'property',
      },
    ],
  },
];
const secondBatch = [
  {
    code: 'marketing',
    label: 'Marketing',
    children: [
      {
        code: 'name',
        label: 'Nom',
        type: 'attribute',
      },
      {
        code: 'description',
        label: 'Description',
        type: 'attribute',
      },
    ],
  },
];

jest.mock('@akeneo-pim-community/shared/lib/hooks/useUserContext', () => ({
  useUserContext: () => ({get: () => 'en_US'}),
}));
const mockedFetcher = (offset: SourceOffset) => {
  switch (offset.attribute) {
    case 0:
      return {results: firstBatch, offset: {attribute: 1, association_type: 0, system: 0}};
    case 1:
      return {results: secondBatch, offset: {attribute: 2, association_type: 0, system: 0}};
    default:
      return {results: [], offset: {attribute: 3, association_type: 0, system: 0}};
  }
};
jest.mock('./useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => mockedFetcher,
}));

const flushPromises = () => new Promise(setImmediate);

test('It can offset available source results', async () => {
  const {result} = renderHook(() => useOffsetAvailableSources('search value'));
  const [results] = result.current;
  expect(results).toEqual([]);

  await act(async () => {
    await flushPromises();
    let [updatedResults, handleNextPage] = result.current;
    expect(updatedResults).toEqual(firstBatch);

    // Fetching second batch
    handleNextPage();
    await flushPromises();
    [updatedResults, handleNextPage] = result.current;
    expect(updatedResults).toEqual([...firstBatch, ...secondBatch]);

    // Fetching third batch, will stay the same as the fetcher returns nothing
    handleNextPage();
    await flushPromises();
    [updatedResults, handleNextPage] = result.current;
    expect(updatedResults).toEqual([...firstBatch, ...secondBatch]);

    // Still trying to fetch but won't because we are on last page
    handleNextPage();
    await flushPromises();
    [updatedResults] = result.current;
    expect(updatedResults).toEqual([...firstBatch, ...secondBatch]);
  });
});

test('It does not update results if unmounted', async () => {
  const {result, unmount} = renderHook(() => useOffsetAvailableSources('search value'));

  unmount();

  await act(async () => {
    await flushPromises();
    const [updatedResults] = result.current;
    expect(updatedResults).toEqual([]);
  });
});

test('It does not update results if the shouldFetch param is set to false', async () => {
  const {result} = renderHook(() => useOffsetAvailableSources('search value', false));

  await act(async () => {
    await flushPromises();
    const [updatedResults] = result.current;
    expect(updatedResults).toEqual([]);
  });
});

test('It goes back to first page when search value changes', async () => {
  const {result, rerender} = renderHook(({searchValue}) => useOffsetAvailableSources(searchValue), {
    initialProps: {searchValue: ''},
  });

  await act(async () => {
    await flushPromises();
    const [firstPageResults] = result.current;
    expect(firstPageResults).toEqual(firstBatch);
  });

  await act(async () => {
    rerender({searchValue: 'another_one'});

    await flushPromises();
    const [resetPageResults] = result.current;
    expect(resetPageResults).toEqual(firstBatch);
  });
});
