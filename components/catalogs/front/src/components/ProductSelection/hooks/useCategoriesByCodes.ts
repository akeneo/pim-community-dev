import {useEffect, useState} from 'react';
import {useCategories} from './useCategories';
import {Category, CategoryCode} from '../models/Category';

type Cache = {
    [key: CategoryCode]: Category;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Category[];
    error: Error;
};

const LIMIT = 20;

export const useCategoriesByCodes = (codes: CategoryCode[]): Result => {
    const [cache, setCache] = useState<Cache>({});
    const cachedCodes = Object.keys(cache);
    const unknownCodes = codes.filter(code => cachedCodes.indexOf(code) === -1);
    const slicedUnknownCodes = unknownCodes.slice(0, LIMIT);

    const {data: categories, isLoading, isError, error} = useCategories(slicedUnknownCodes);

    useEffect(() => {
        if (categories === undefined) {
            return;
        }

        const newCategories = categories
            .filter(category => !cachedCodes.includes(category.code))
            .reduce(
                (list, category) => ({
                    ...list,
                    [category.code]: category,
                }),
                {}
            );

        if (Object.keys(newCategories).length > 0) {
            setCache(cache => ({
                ...cache,
                ...newCategories,
            }));
        }
    }, [cachedCodes, categories, setCache]);

    const categoriesFromCodes = Object.values(cache).filter(v => codes.includes(v.code));

    return {
        isLoading: isLoading,
        isError: isError,
        data: categoriesFromCodes,
        error: error,
    };
};
