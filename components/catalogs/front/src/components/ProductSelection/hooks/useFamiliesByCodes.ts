import {useEffect, useState} from 'react';
import {useInfiniteFamilies} from './useInfiniteFamilies';
import {Family} from '../models/Family';

type Cache = {
    [key: string]: Family;
};

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Family[] | undefined;
    error: Error;
};

const LIMIT = 20;

export const useFamiliesByCodes = (codes: string[]): Result => {
    const [cache, setCache] = useState<Cache>({});

    const cachedCodes = Object.keys(cache);
    const unknownCodes = codes.filter(code => cachedCodes.indexOf(code) === -1);
    const slicedUnknownCodes = unknownCodes.slice(0, LIMIT);

    const {data: families} = useInfiniteFamilies({
        codes: slicedUnknownCodes,
        limit: LIMIT,
    });

    useEffect(() => {
        if (families === undefined) {
            return;
        }

        const newFamilies = families
            .filter(family => !cachedCodes.includes(family.code))
            .reduce(
                (list, family) => ({
                    ...list,
                    [family.code]: family,
                }),
                {}
            );

        if (Object.keys(newFamilies).length > 0) {
            setCache(cache => ({
                ...cache,
                ...newFamilies,
            }));
        }
    }, [cachedCodes, families, setCache]);

    return {
        isLoading: true,
        isError: false,
        data: Object.values(cache),
        error: null,
    };
};
