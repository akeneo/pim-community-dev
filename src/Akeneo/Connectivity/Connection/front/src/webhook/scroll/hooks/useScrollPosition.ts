import { DependencyList, RefObject, useCallback, useEffect, useLayoutEffect } from 'react';
import debounce from '../../../shared/utils/debounce';
import findScrollParent from '../utils/findScrollParent';

export type ScrollPosition = {
    scrollTop: number;
    clientHeight: number;
    scrollHeight: number;
}

const useScrollPosition = (
    ref: RefObject<HTMLElement>,
    callback: (position: ScrollPosition) => void,
    deps: DependencyList = [],
    delay = 300
): void => {
    const handleScroll = useCallback(() => {
        if (null === ref.current) {
            return;
        }

        const scrollParent = findScrollParent(ref.current);
        const { scrollTop, clientHeight, scrollHeight } = scrollParent;

        callback({
            scrollTop,
            clientHeight,
            scrollHeight,
        });
    }, [...deps, ref, callback]);

    const debounceHandleScroll = debounce(handleScroll, delay);

    // We force the callback each time the deps changes,
    // this way, the up-to-date scrollPosition is sent even when the user does not scroll
    // but something changed (lines were removed for example, or any other case).
    useEffect(() => {
        debounceHandleScroll();
    }, deps);

    useLayoutEffect(() => {
        window.addEventListener('scroll', debounceHandleScroll, true);

        return () => window.removeEventListener('scroll', debounceHandleScroll);
    }, [debounceHandleScroll]);
};

export default useScrollPosition;
