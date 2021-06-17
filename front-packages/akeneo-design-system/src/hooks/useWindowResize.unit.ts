import {act, renderHook} from '@testing-library/react-hooks';
import {useWindowResize} from './useWindowResize';
import {fireEvent} from '@testing-library/dom';

const resizeWindow = (height: number, width: number) => {
  Object.assign(window, {innerWidth: width, innerHeight: height});

  fireEvent(window, new Event('resize'));
};

test('It can register listener on keyboard events', () => {
  const {result} = renderHook(() => useWindowResize());

  expect(result.current).toEqual({height: window.innerHeight, width: window.innerWidth});

  void act(() => {
    resizeWindow(500, 500);
  });

  expect(result.current).toEqual({height: 500, width: 500});
});
