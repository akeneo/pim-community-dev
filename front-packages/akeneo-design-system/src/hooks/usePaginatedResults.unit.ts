import {renderHook, act} from '@testing-library/react-hooks';
import {usePaginatedResults} from './usePaginatedResults';

const fetcher = jest.fn((page: number) => {
  return new Promise<string[]>(resolve => {
    setTimeout(() => {
      resolve([`nice_item_${page}`]);
    }, 10);
  });
});

// test('It can fetch paginated results', async () => {
//   jest.useFakeTimers();
//   const {result} = renderHook(() => usePaginatedResults<string>(fetcher, []));
//   const [results] = result.current;
//   expect(results).toEqual([]);
//   expect(fetcher).toHaveBeenCalledWith(0);

//   await act(async () => {
//     await jest.runAllTimers();
//     const [updatedResults, handleNextPage] = result.current;
//     expect(updatedResults).toEqual(['nice_item_0']);

//     handleNextPage();
//     await jest.runAllTimers();
//     expect(result.current[0]).toEqual(['nice_item_0', 'nice_item_1']);
//   });
// });

// test('It does not fetch if there is already a fetch running', async () => {
//   jest.useFakeTimers();
//   const {result} = renderHook(() => usePaginatedResults<string>(fetcher, []));
//   const [, handleNextPage] = result.current;

//   await act(async () => {
//     setTimeout(() => {
//       // We call handle next page before the fetcher answered
//       handleNextPage();
//     });
//     await jest.runAllTimers();
//     const [updatedResults] = result.current;
//     expect(updatedResults).toEqual(['nice_item_0']);
//   });
// });

// test('It does not update results if unmounted', async () => {
//   jest.useFakeTimers();
//   const {result, unmount} = renderHook(() => usePaginatedResults<string>(fetcher, []));
//   unmount();

//   await act(async () => {
//     await jest.runAllTimers();
//     const [updatedResults] = result.current;
//     expect(updatedResults).toEqual([]);
//   });
// });

// test('It go back to first page if one of the dependencies is updated', async () => {
//   jest.useFakeTimers();
//   const {result, rerender} = renderHook<
//     {fetcher: (page: number) => Promise<string[]>; dependencies: string[]},
//     readonly [string[], () => void]
//   >(({fetcher, dependencies}) => usePaginatedResults<string>(fetcher, dependencies), {
//     initialProps: false,
//   });

//   await act(async () => {
//     await jest.runAllTimers();
//     const [firstPageResults, handleNextPage] = result.current;
//     expect(firstPageResults).toEqual(['nice_item_0']);

//     handleNextPage();
//     await jest.runAllTimers();
//     const [secondPageResults] = result.current;
//     expect(secondPageResults).toEqual(['nice_item_0', 'nice_item_1']);

//     rerender({fetcher, dependencies: ['nice']});
//     await jest.runAllTimers();
//     const [resetPageResults] = result.current;
//     expect(resetPageResults).toEqual(['nice_item_0']);
//   });
// });

test('It go back to first page if one of the dependencies is updated', async () => {
  jest.useFakeTimers();
  const searchValueRef = {current: ''};
  const {result, rerender} = renderHook(
    ({searchValue}: {searchValue: {current: string}}) => usePaginatedResults<string>(fetcher, [searchValue.current]),
    {
      initialProps: {searchValue: searchValueRef},
    }
  );

  await act(async () => {
    await jest.runAllTimers();
    const [firstPageResults, handleNextPage] = result.current;
    expect(firstPageResults).toEqual(['nice_item_0']);

    handleNextPage();
    await jest.runAllTimers();
    const [secondPageResults] = result.current;
    expect(secondPageResults).toEqual(['nice_item_0', 'nice_item_1']);

    searchValueRef.current = 'nice';
    rerender({searchValue: searchValueRef});
    await jest.runAllTimers();

    const [resetPageResults] = result.current;
    expect(resetPageResults).toEqual(['nice_item_0']);
  });
});
