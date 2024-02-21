import debounce from '@src/shared/utils/debounce';

beforeEach(() => {
    jest.useFakeTimers('modern');
});
afterAll(() => {
    jest.useRealTimers();
});

it('use the debounced callback', () => {
    const callback = jest.fn();

    const debouncedFunc = debounce(callback, 50);

    debouncedFunc();
    jest.advanceTimersByTime(50);

    expect(callback).toBeCalled();
});
