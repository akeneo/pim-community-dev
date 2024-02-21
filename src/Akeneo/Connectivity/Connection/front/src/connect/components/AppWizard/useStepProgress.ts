import {useState} from 'react';

export const useStepProgress = <T extends unknown>(steps: T[]) => {
    if (steps.length === 0) {
        throw new Error('At least one step is required');
    }

    const [index, setIndex] = useState(0);

    const previous = () => setIndex(index => (index === 0 ? index : index - 1));
    const next = () => setIndex(index => (index === steps.length - 1 ? index : index + 1));

    return {
        previous,
        next,
        current: steps[index],
        isFirst: index === 0,
        isLast: index === steps.length - 1,
    };
};
