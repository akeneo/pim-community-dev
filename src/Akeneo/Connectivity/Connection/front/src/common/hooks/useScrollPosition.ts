import { DependencyList, RefObject, useCallback, useEffect, useLayoutEffect } from 'react';
import { debounceCallback } from '@akeneo-pim-community/shared';
import { findScrollParent } from './useScrollParent';

export type ScrollPosition = {
    scrollTop: number;
    clientHeight: number;
    scrollHeight: number;
}

const useScrollPosition = (
    ref: RefObject<HTMLElement>,
    callback: (position: ScrollPosition) => void,
    deps: DependencyList = [],
    delay: number = 300
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

    const debounceHandleScroll = debounceCallback(handleScroll, delay);

    useEffect(() => {
        debounceHandleScroll();
    }, deps);

    useLayoutEffect(() => {
        window.addEventListener('scroll', debounceHandleScroll, true);

        return () => window.removeEventListener('scroll', debounceHandleScroll);
    }, [debounceHandleScroll]);
};

export default useScrollPosition;
