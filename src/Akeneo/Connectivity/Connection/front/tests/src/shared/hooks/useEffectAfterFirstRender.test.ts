import {renderHook} from '@testing-library/react-hooks';
import {useEffectAfterFirstRender} from '@src/shared/hooks/useEffectAfterFirstRender';

test('useEffect is executed only after the first render', () => {
    const callback = jest.fn();
    let dependency = 0;

    const {rerender} = renderHook(() => useEffectAfterFirstRender(callback, [dependency]));

    // After the initial render, the callback is not called.
    expect(callback).not.toHaveBeenCalled();

    // After another render, the callback is not called either, due to the dependencies.
    rerender();
    expect(callback).not.toHaveBeenCalled();

    // We now change a dependency, and call the rerender, the callback should be called.
    dependency++;
    rerender();
    expect(callback).toHaveBeenCalledTimes(1);
});
