import {DependencyList, RefObject, useCallback, useLayoutEffect} from 'react';
import debounce from '../../../shared/utils/debounce';
import findScrollParent from '../utils/findScrollParent';

export type ScrollPosition = {
    scrollTop: number;
    clientHeight: number;
    scrollHeight: number;
};

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
        const {scrollTop, clientHeight, scrollHeight} = scrollParent;

        callback({
            scrollTop,
            clientHeight,
            scrollHeight,
        });
    }, [...deps, ref, callback]);

    const debounceHandleScroll = debounce(handleScroll, delay);

    useLayoutEffect(() => {
        window.addEventListener('scroll', debounceHandleScroll, true);

        return () => window.removeEventListener('scroll', debounceHandleScroll);
    }, [debounceHandleScroll]);
};

export default useScrollPosition;
