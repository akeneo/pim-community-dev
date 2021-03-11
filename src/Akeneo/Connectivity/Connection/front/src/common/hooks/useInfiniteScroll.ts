import {RefObject, useEffect, useState} from 'react';
import useScrollPosition from './useScrollPosition';

type State = {
    lastFetchedTopPosition: number|null,
    isFirstFetchDone: boolean
}

export const useInfiniteScroll = <T>(
    fetchNextPage: (prev?: T) => Promise<void>,
    scrollContainerRef: RefObject<HTMLElement>,
    threshold: number = 800
): void => {
    const [state, setState] = useState<State>({
        lastFetchedTopPosition: null,
        isFirstFetchDone: false
    });
    const scrollPosition = useScrollPosition(scrollContainerRef);
    if (null !== scrollPosition && null === state.lastFetchedTopPosition) {
        setState({...state, lastFetchedTopPosition: scrollPosition.top});
    }
    let isDistanceReached = false;
    if (null !== scrollPosition && null !== state.lastFetchedTopPosition) {
        const distance = scrollPosition.top - state.lastFetchedTopPosition;
        isDistanceReached = Math.abs(distance) >= threshold && distance < 0;

        //console.log(`height: ${scrollPosition.height}`);
        //console.log(`last top: ${state.lastFetchedTopPosition}`);
        //console.log(`top: ${scrollPosition.top}`);
        //console.log(`y: ${scrollPosition.y}`);
        //console.log(`top: ${scrollPosition.top}`);
        //console.log(`result: ${distance}`);
    }

    // check
    useEffect(() => {
        if (!state.isFirstFetchDone) {
            fetchNextPage();
            setState({...state, isFirstFetchDone: true});
        }
    }, [state, fetchNextPage]);

    useEffect(() => {
        if (isDistanceReached && null !== scrollPosition) {
            setState({...state, lastFetchedTopPosition: scrollPosition.top});
            fetchNextPage();
        }
    }, [isDistanceReached, scrollPosition]);
};
