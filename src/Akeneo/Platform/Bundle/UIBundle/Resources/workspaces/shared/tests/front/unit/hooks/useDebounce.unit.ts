import {act, renderHook} from '@testing-library/react-hooks';
import {useDebounce, useDebounceCallback} from '../../../../src/hooks/useDebounce';

describe('useDebounce', () => {
  beforeEach(() => {
    jest.useFakeTimers('modern');
  });
  afterAll(() => {
    jest.useRealTimers();
  });

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

describe('useDebounceCallback', () => {
  beforeEach(() => {
    jest.useFakeTimers('modern');
  });
  afterAll(() => {
    jest.useRealTimers();
  });

  it('use the debounced callback', () => {
    const callback = jest.fn();

    const {result} = renderHook(() => useDebounceCallback(callback, 100));

    act(() => {
      result.current();
      jest.advanceTimersByTime(100);
    });
    expect(callback).toBeCalled();
  });

  it('use the debounced callback after 100ms', () => {
    type TestedCallback = (value: string) => void;
    const callback: TestedCallback = jest.fn();

    const {result} = renderHook(() => useDebounceCallback(callback, 100));

    act(() => {
      result.current('t');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('ty');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typ');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typi');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typin');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typing');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      jest.advanceTimersByTime(100);
    });
    expect(callback).toBeCalledWith('typing');
  });
});
