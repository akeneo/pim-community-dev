import {renderHook} from '@testing-library/react-hooks';
import useIsMounted from '@src/webhook/scroll/hooks/useIsMounted';

test('It can tell if a component is mounted or not', () => {
    const {result, unmount} = renderHook(() => useIsMounted());

    expect(result.current()).toBe(true);

    unmount();

    expect(result.current()).toBe(false);
});
