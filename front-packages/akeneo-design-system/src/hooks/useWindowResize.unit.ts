import {act, renderHook} from '@testing-library/react-hooks';
import {fireEvent} from '../storybook/test-util';
import {useWindowResize} from './useWindowResize';

const resizeWindow = (height: number, width: number) => {
  Object.assign(window, {innerWidth: width, innerHeight: height});

  fireEvent(window, new Event('resize'));
};

test('It can tell when the window is resized', () => {
  const {result} = renderHook(() => useWindowResize());

  expect(result.current).toEqual({height: window.innerHeight, width: window.innerWidth});

  void act(() => {
    resizeWindow(500, 500);
  });

  expect(result.current).toEqual({height: 500, width: 500});
});
