import {renderHook} from '@testing-library/react-hooks';
import {usePagination} from './usePagination';
import {RefObject} from 'react';

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

test('it calls the next page handler when the last element is almost reached', () => {
  const handleNextPage = jest.fn();
  const containerRef = {
    current: {},
  } as RefObject<HTMLElement>;
  const lastItemRef = {
    current: {},
  } as RefObject<HTMLElement>;

  renderHook(() => usePagination(containerRef, lastItemRef, handleNextPage, true));

  entryCallback?.([{isIntersecting: true}]);

  expect(handleNextPage).toHaveBeenCalled();
});
