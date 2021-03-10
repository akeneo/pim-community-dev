import {RefObject, useEffect, useLayoutEffect, useState} from 'react';

const useScrollPosition = (element: RefObject<HTMLElement>): DOMRect|null => {
    const [position, setPosition] = useState<DOMRect|null>(null);

    useLayoutEffect(() => {
        const handleScroll = () => {
            const position = element.current?.getBoundingClientRect();
            setPosition(position ? position : null);
        };

        window.addEventListener('scroll', handleScroll, true);

        return () => window.removeEventListener('scroll', handleScroll);
    }, [element]);

    return position;
};

export default useScrollPosition;
