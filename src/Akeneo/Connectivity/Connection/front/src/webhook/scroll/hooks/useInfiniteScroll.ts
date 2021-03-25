import {RefObject, useCallback, useEffect, useState} from 'react';
import useScrollPosition, {ScrollPosition} from './useScrollPosition';
import useIsMounted from './useIsMounted';

type InfiniteScrollStatus = {
    isLoading: boolean;
    reset: () => void;
};

/**
 * Infinite scroll with the next fetch automically triggered when reaching the bottom the container scroll view
 *
 * The loadNextPage callback should return either the next page or null.
 * If null is returned, the hook will assume that the scroll reached the end of the pages and will stop calling it.
 * The callback can use the previous one, given as an argument, to load its data (for example, the previous response
 * can contain a search_after parameter).
 *
 * The container argument is used to find the scrollable element, to watch for the scroll position of its content.
 */
const useInfiniteScroll = <T>(
    loadNextPage: (prev: T | null) => Promise<T | null>,
    containerRef: RefObject<HTMLElement>,
    threshold = 300
): InfiniteScrollStatus => {
    const [state, setState] = useState<{
        lastPage: T | null;
        isStopped: boolean;
        isLoading: boolean;
        shouldFetch: boolean;
    }>({
        lastPage: null,
        isStopped: false,
        isLoading: false,
        shouldFetch: true,
    });

    const {lastPage, isStopped, isLoading, shouldFetch} = state;

    const isMounted = useIsMounted();

    useEffect(() => {
        (async () => {
            if (!shouldFetch || isLoading || isStopped) {
                return;
            }

            setState(state => ({
                ...state,
                isLoading: true,
            }));

            const page = await loadNextPage(lastPage);

            // When using useEffect + async, we can have the case where the following setState is called
            // when the component has been unmounted.
            // This check prevent React to thrown on this error.
            if (!isMounted()) {
                return;
            }

            setState(state => ({
                ...state,
                lastPage: page,
                isStopped: null === page,
                isLoading: false,
                shouldFetch: false,
            }));
        })();
    }, [shouldFetch]);

    const handleScrollPosition = useCallback(
        (scrollPosition: ScrollPosition) => {
            if (!isMounted()) {
                return;
            }

            const {scrollTop, clientHeight, scrollHeight} = scrollPosition;
            // see https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollHeight
            // basically, clientHeight is the visible height.
            // scrollTop is the distance between the top and the visible part.
            // scrollHeight is real height of the content, including content outside the view.
            // The trigger is reached when, the content is smaller than the available container,
            // or when we are at #{threshold}px of the bottom.
            const scrollThresholdIsReached = scrollHeight <= scrollTop + clientHeight + threshold;

            if (scrollThresholdIsReached) {
                setState(state => ({
                    ...state,
                    shouldFetch: true,
                }));
            }
        },
        [threshold]
    );

    // This hook will call our callback, with a 100ms debounce, each time the container is scrolled.
    // By giving it an array of dependencies with the lastPage, each time we load a new page,
    // it will recompute the scroll position to be sure that there is enough visible content.
    //
    // The issue resolved by this is the following one:
    // If your screen is too big, and one page of content is not enough to trigger the overflow of the container,
    // there is no scrollbar, you cannot scroll, the following pages are never requested.
    useScrollPosition(containerRef, handleScrollPosition, [lastPage], 100);

    const reset = useCallback(() => {
        setState({
            lastPage: null,
            isStopped: false,
            isLoading: false,
            shouldFetch: false,
        });
        setState(state => ({
            ...state,
            shouldFetch: true,
        }));
    }, []);

    return {
        isLoading: isLoading,
        reset: reset,
    };
};

export default useInfiniteScroll;
