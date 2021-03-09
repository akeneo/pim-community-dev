import { useLayoutEffect, useState } from 'react';

const useScrollPosition = (): DOMRect|null => {
    const [position, setPosition] = useState<DOMRect|null>(null);

    useLayoutEffect(() => {
        const handleScroll = () => {
            setPosition(document.body.getBoundingClientRect());
        };

        window.addEventListener('scroll', handleScroll);

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return position;
};

export default useScrollPosition;
