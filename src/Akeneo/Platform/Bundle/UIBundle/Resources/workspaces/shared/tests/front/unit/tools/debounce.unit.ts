import {act, renderHook} from '@testing-library/react-hooks';
import {debounceCallback} from '../../../../src';

describe('debounceCallback', () => {
  beforeEach(() => {
    jest.useFakeTimers('modern');
  });
  afterAll(() => {
    jest.useRealTimers();
  });

  it('use the debounced callback', () => {
    const callback = jest.fn();

    const debouncedFunc = debounceCallback(callback, 100);

    debouncedFunc();
    jest.advanceTimersByTime(100);

    expect(callback).toBeCalled();
  });

  it('use the debounced callback after 100ms', () => {
    type TestedCallback = (value: string) => void;
    const callback: TestedCallback = jest.fn();

    const debouncedFunc = debounceCallback(callback, 100);

    debouncedFunc('t');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    debouncedFunc('ty');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    debouncedFunc('typ');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    debouncedFunc('typi');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    debouncedFunc('typin');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    debouncedFunc('typing');
    jest.advanceTimersByTime(10);
    expect(callback).not.toBeCalled();

    jest.advanceTimersByTime(100);
    expect(callback).toBeCalledWith('typing');
  });
});
