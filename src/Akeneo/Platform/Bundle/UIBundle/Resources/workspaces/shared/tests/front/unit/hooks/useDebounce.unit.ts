import {act, renderHook} from '@testing-library/react-hooks';
import useDebounce from '../../../../src/hooks/useDebounce';

jest.useFakeTimers();
describe('useDebounce', () => {
  it('use the debounced value after 100ms', () => {
    const {result, rerender} = renderHook<{value: string; delay: number}, string>(({value, delay}) =>
      useDebounce(value, delay)
    );

    const delay = 100;
    act(() => {
      rerender({value: 't', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      rerender({value: 'ty', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      rerender({value: 'typ', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      rerender({value: 'typi', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      rerender({value: 'typin', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      rerender({value: 'typing', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    act(() => {
      jest.advanceTimersByTime(100);
    });
    expect(result.current).toBe('typing');
  });
});
