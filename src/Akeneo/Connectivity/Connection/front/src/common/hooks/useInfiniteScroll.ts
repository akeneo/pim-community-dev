import {RefObject, useEffect, useState} from 'react';
import useScrollPosition from './useScrollPosition';

type State = {
    lastFetchedTopPosition: number,
    isFirstFetchDone: boolean
}

export const useInfiniteScroll = <T>(
    fetchNextPage: (prev?: T) => Promise<T | undefined>,
    scrollContainerRef: RefObject<HTMLElement>,
    threshold: number = 1000
): void => {
    const [state, setState] = useState<State>({
        lastFetchedTopPosition: 0,
        isFirstFetchDone: false
    });
    const scrollPosition = useScrollPosition(scrollContainerRef);
    let isDistanceReached = false;
    if (null !== scrollPosition && null !== state.lastFetchedTopPosition) {
        const result = Math.abs(scrollPosition.top) - state.lastFetchedTopPosition;
        console.log(`height: ${scrollPosition.height}`);
        console.log(`last top: ${state.lastFetchedTopPosition}`);
        console.log(`top: ${scrollPosition.top}`);
        console.log(`y: ${scrollPosition.y}`);
        //console.log(`top: ${scrollPosition.top}`);
        console.log(`result: ${result}`);
        isDistanceReached = result >= threshold;
    }

    useEffect(() => {
        if (!state.isFirstFetchDone) {
            fetchNextPage();
            setState({...state, isFirstFetchDone: true});
        }
    }, [state, fetchNextPage]);

    useEffect(() => {
        if (isDistanceReached && null !== scrollPosition) {
            fetchNextPage();
            setState({...state, lastFetchedTopPosition: Math.abs(scrollPosition.top)});
        }
    }, [isDistanceReached, scrollPosition]);
};
