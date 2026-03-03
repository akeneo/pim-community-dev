import {useStepProgress} from '@src/connect/components/AppWizard/useStepProgress';
import {act, renderHook} from '@testing-library/react-hooks';

test('it goes forward on all the steps', () => {
    const steps = ['step 1', 'step 2', 'step 3'];
    const {result} = renderHook(() => useStepProgress(steps));

    expect(result.current.current).toBe('step 1');
    expect(result.current.isFirst).toBe(true);
    expect(result.current.isLast).toBe(false);

    act(() => {
        result.current.next();
    });

    expect(result.current.current).toBe('step 2');
    expect(result.current.isFirst).toBe(false);
    expect(result.current.isLast).toBe(false);

    act(() => {
        result.current.next();
    });

    expect(result.current.current).toBe('step 3');
    expect(result.current.isFirst).toBe(false);
    expect(result.current.isLast).toBe(true);
});

test('it goes backward on all the steps', () => {
    const steps = ['step 1', 'step 2', 'step 3'];
    const {result} = renderHook(() => useStepProgress(steps));

    act(() => {
        result.current.next();
        result.current.next();
    });

    expect(result.current.current).toBe('step 3');
    expect(result.current.isFirst).toBe(false);
    expect(result.current.isLast).toBe(true);

    act(() => {
        result.current.previous();
    });

    expect(result.current.current).toBe('step 2');
    expect(result.current.isFirst).toBe(false);
    expect(result.current.isLast).toBe(false);

    act(() => {
        result.current.previous();
    });

    expect(result.current.current).toBe('step 1');
    expect(result.current.isFirst).toBe(true);
    expect(result.current.isLast).toBe(false);
});

test('it stops at the last step when next() is called', () => {
    const steps = ['step 1', 'step 2'];
    const {result} = renderHook(() => useStepProgress(steps));

    act(() => {
        result.current.next();
    });

    expect(result.current.current).toBe('step 2');

    act(() => {
        result.current.next();
    });

    expect(result.current.current).toBe('step 2');
});

test('it stops at the first step when previous() is called', () => {
    const steps = ['step 1', 'step 2'];
    const {result} = renderHook(() => useStepProgress(steps));

    expect(result.current.current).toBe('step 1');

    act(() => {
        result.current.previous();
    });

    expect(result.current.current).toBe('step 1');
});

test('it throws if there is no step provided', () => {
    const steps: string[] = [];
    const {result} = renderHook(() => useStepProgress(steps));

    expect(result.error.message).toBe('At least one step is required');
});
