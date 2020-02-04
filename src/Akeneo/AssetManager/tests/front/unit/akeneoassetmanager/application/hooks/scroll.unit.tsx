'use strict';

import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useScroll, useKeepVisibleX} from 'akeneoassetmanager/application/hooks/scroll';

describe('Test scrolling hooks', () => {
  test("useScroll resets the given element's scroll position", async () => {
    const element = {scrollTop: 20};
    const {result} = renderHook(() => useScroll());

    result.current[0].current = element;
    result.current[1]();

    expect(element.scrollTop).toEqual(0);
  });

  test('useKeepVisibleX keeps the given element visible within the given container', async () => {
    Object.defineProperty(window, 'getComputedStyle', {
      value: () => ({
        marginLeft: 0,
        marginRight: 0,
      }),
    });

    const element = (width: number, left: number, right: number) => ({
      getBoundingClientRect: () => ({width, left, right}),
    });
    const container = {scrollLeft: 30, getBoundingClientRect: () => ({left: 0, right: 100})};

    const {result} = renderHook(() => useKeepVisibleX());
    result.current.containerRef.current = container;

    result.current.elementRef(element(10, -10, 90));
    expect(container.scrollLeft).toEqual(20);

    result.current.elementRef(element(20, 120, 90));
    expect(container.scrollLeft).toEqual(60);
  });
});
