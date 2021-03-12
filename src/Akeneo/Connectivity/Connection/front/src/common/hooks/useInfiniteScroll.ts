import { RefObject, useCallback, useEffect, useState } from 'react';
import { useIsMounted } from '@akeneo-pim-community/shared';
import useScrollPosition, { ScrollPosition } from './useScrollPosition';

type InfiniteScrollStatus = {
    isLoading: boolean;
};

export const useInfiniteScroll = <T>(
    loadNextPage: (prev: T|null) => Promise<T|null>,
    containerRef: RefObject<HTMLElement>,
    threshold: number = 300
): InfiniteScrollStatus => {
    const [state, setState] = useState<{
        lastPage: T|null,
        isStopped: boolean,
        isLoading: boolean,
        shouldFetch: boolean,
    }>({
        lastPage: null,
        isStopped: false,
        isLoading: false,
        shouldFetch: true,
    });

    const { lastPage, isStopped, isLoading, shouldFetch } = state;

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

    const handleScrollPosition = useCallback((scrollPosition: ScrollPosition) => {
        const { scrollTop, clientHeight, scrollHeight } = scrollPosition;
        const scrollThresholdIsReached = scrollHeight <= scrollTop + clientHeight + threshold;

        if (scrollThresholdIsReached) {
            setState(state => ({
                ...state,
                shouldFetch: true,
            }));
        }
    }, [threshold]);

    useScrollPosition(containerRef, handleScrollPosition, [lastPage], 300);

    return {
        isLoading: isLoading,
    };
};
