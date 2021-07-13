import {RefObject, useCallback, useState} from 'react';
import useIsMounted from './useIsMounted';
import useScrollPosition from './useScrollPosition';
import {ScrollPosition} from '../utils/getScrollPosition';

export const useDisplayScrollTopButton = (element: RefObject<HTMLElement>) => {
    const isMounted = useIsMounted();
    const [displayButton, setDisplayButton] = useState<boolean>(false);

    const handleScrollPosition = useCallback((scrollPosition: ScrollPosition) => {
        if (!isMounted()) {
            return;
        }

        const {scrollTop} = scrollPosition;
        setDisplayButton(0 !== scrollTop);
    }, []);

    useScrollPosition(element, handleScrollPosition, [], 0);

    return displayButton;
};
