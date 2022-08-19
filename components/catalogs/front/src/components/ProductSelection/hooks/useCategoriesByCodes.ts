import {useEffect, useState} from 'react';
import {useCategories} from './useCategories';
import {Category, CategoryCode} from '../models/Category';

type Cache = {
    [key: CategoryCode]: Category;
};
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Category[];
    error: ResultError;
};

const LIMIT = 20;

export const useCategoriesByCodes = (codes: CategoryCode[]): Result => {
    const [cache, setCache] = useState<Cache>({});
    const cachedCodes = Object.keys(cache);
    const unknownCodes = codes.filter(code => cachedCodes.indexOf(code) === -1);
    const slicedUnknownCodes = unknownCodes.slice(0, LIMIT);

    const {data: categories, isLoading, isError, error} = useCategories({codes: slicedUnknownCodes});

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

    const categoriesFromCodes = codes.map(
        categoryCode =>
            cache[categoryCode] ?? {
                code: categoryCode,
                label: `[${categoryCode}]`,
                isLeaf: true,
            }
    );

    return {
        isLoading: isLoading,
        isError: isError,
        data: categoriesFromCodes,
        error: error,
    };
};
