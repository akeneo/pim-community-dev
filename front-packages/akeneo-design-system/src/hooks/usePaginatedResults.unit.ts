import {renderHook, act} from '@testing-library/react-hooks';
import {usePaginatedResults} from './usePaginatedResults';

const fetcher = jest.fn(
  (page: number) =>
    new Promise<string[]>(resolve => {
      if (2 === page) resolve([]);
      resolve([`nice_item_${page}`]);
    })
);

jest.useFakeTimers();

const flushPromises = () => new Promise(setImmediate);

test('It can fetch paginated results', async () => {
  const {result} = renderHook(() => usePaginatedResults<string>(fetcher, []));
  const [results] = result.current;
  expect(results).toEqual([]);
  expect(fetcher).toHaveBeenCalledWith(0);

  await act(async () => {
    jest.runAllTimers();
    await flushPromises();
    const [updatedResults, handleNextPage] = result.current;
    expect(updatedResults).toEqual(['nice_item_0']);

    handleNextPage();
    jest.runAllTimers();
    await flushPromises();
    expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);

    handleNextPage();
    jest.runAllTimers();
    await flushPromises();
    expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);
  });
});

test('It does not fetch if there is already a fetch running', async () => {
  const {result} = renderHook(() => usePaginatedResults<string>(fetcher, []));
  const [, handleNextPage] = result.current;

  await act(async () => {
    setTimeout(() => {
      // We call handle next page before the fetcher answered
      handleNextPage();
    });
    jest.runAllTimers();
    await flushPromises();
    const [updatedResults] = result.current;
    expect(updatedResults).toEqual(['nice_item_0']);
  });
});

test('It does not update results if unmounted', async () => {
  const {result, unmount} = renderHook(() => usePaginatedResults<string>(fetcher, []));

  unmount();

  await act(async () => {
    jest.runAllTimers();
    await flushPromises();
    const [updatedResults] = result.current;
    expect(updatedResults).toEqual([]);
  });
});

test('It does not update results if the shouldFetch param is set to false', async () => {
  const {result} = renderHook(() => usePaginatedResults<string>(fetcher, [], false));

  await act(async () => {
    jest.runAllTimers();
    await flushPromises();
    const [updatedResults] = result.current;
    expect(updatedResults).toEqual([]);
    expect(fetcher).not.toBeCalled();
  });
});

test('It goes back to first page when dependencies change', async () => {
  const {result, rerender} = renderHook(({searchValue}) => usePaginatedResults<string>(fetcher, [searchValue]), {
    initialProps: {searchValue: ''},
  });

  await act(async () => {
    jest.runAllTimers();
    await flushPromises();
    const [firstPageResults, handleNextPage] = result.current;
    expect(firstPageResults).toEqual(['nice_item_0']);

    handleNextPage();
    jest.runAllTimers();
    await flushPromises();
    const [secondPageResults] = result.current;
    expect(secondPageResults).toEqual(['nice_item_0', 'nice_item_1']);
  });

  await act(async () => {
    rerender({searchValue: 'nice'});
    jest.runAllTimers();
    await flushPromises();
    const [resetPageResults] = result.current;
    expect(resetPageResults).toEqual(['nice_item_0']);
  });
});
