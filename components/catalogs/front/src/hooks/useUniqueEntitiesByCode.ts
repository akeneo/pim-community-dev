import {useMemo} from 'react';

type Entity = {
    code: string;
};

export const useUniqueEntitiesByCode = <E extends Entity>(first?: E[], second?: E[]): E[] => {
    return useMemo(() => {
        if (first === undefined || first.length === 0) {
            return second || [];
        }

        if (second === undefined || second.length === 0) {
            return first;
        }

        const codes = first.map(entity => entity.code);

        return [...first, ...second.filter(entity => !codes.includes(entity.code))];
    }, [first, second]);
};
