import {useMemo} from 'react';
import {Family} from '../models/Family';

export const useUniqueFamilies = (selection?: Family[], results?: Family[]): Family[] => {
    return useMemo(() => {
        if (selection === undefined || selection.length === 0) {
            return results || [];
        }

        if (results === undefined || results.length === 0) {
            return selection || [];
        }

        const codes = selection.map(family => family.code);

        return [...selection, ...results.filter(family => !codes.includes(family.code))];
    }, [selection, results]);
};
