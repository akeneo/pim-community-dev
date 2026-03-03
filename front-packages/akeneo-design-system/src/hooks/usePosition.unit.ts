import {RefObject} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useVerticalPosition, useHorizontalPosition} from './usePosition';

beforeAll(() => {
  Object.assign(window, {
    innerWidth: 200,
    innerHeight: 200,
  });
});

const getFakeRef = (width: number, height: number, top: number, left: number) =>
  ({
    current: {
      getBoundingClientRect: () => ({
        width,
        height,
        top,
        left,
        bottom: 0,
        right: 0,
      }),
    },
  } as RefObject<HTMLElement>);

test('It returns down when there is enough space above the element', () => {
  const ref = getFakeRef(0, 100, 100, 0);

  const {result} = renderHook(() => useVerticalPosition(ref));

  expect(result.current).toEqual('down');
});

test('It returns down when there is enough space below the element', () => {
  const ref = getFakeRef(0, 100, 50, 0);

  const {result} = renderHook(() => useVerticalPosition(ref));

  expect(result.current).toEqual('down');
});

test('It returns down when there is not enough space below and above the element', () => {
  const ref = getFakeRef(0, 100, 50, 0);

  const {result} = renderHook(() => useVerticalPosition(ref));

  expect(result.current).toEqual('down');
});

test('It returns the forced position when provided', () => {
  const ref = getFakeRef(0, 100, 100, 0);

  const {result} = renderHook(() => useVerticalPosition(ref, 'down'));

  expect(result.current).toEqual('down');
});

test('It returns left when there is more space on the left of the element', () => {
  const ref = getFakeRef(100, 0, 0, 100);

  const {result} = renderHook(() => useHorizontalPosition(ref));

  expect(result.current).toEqual('left');
});

test('It returns right when there is more space on the right the element', () => {
  const ref = getFakeRef(100, 0, 0, 50);

  const {result} = renderHook(() => useHorizontalPosition(ref));

  expect(result.current).toEqual('right');
});

test('It returns the forced position when provided', () => {
  const ref = getFakeRef(100, 0, 0, 100);

  const {result} = renderHook(() => useHorizontalPosition(ref, 'right'));

  expect(result.current).toEqual('right');
});
