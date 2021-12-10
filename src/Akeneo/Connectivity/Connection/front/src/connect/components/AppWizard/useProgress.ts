import {useState} from 'react';

export const useProgress = <T extends unknown>(values: T[]) => {
    const [index, setIndex] = useState(0);

    const previous = () => setIndex(index => (index === 0 ? index : index - 1));
    const next = () => setIndex(index => (index === values.length - 1 ? index : index + 1));

    return {
        previous,
        next,
        current: values[index],
        isFirst: index === 0,
        isLast: index === values.length - 1,
    };
};
