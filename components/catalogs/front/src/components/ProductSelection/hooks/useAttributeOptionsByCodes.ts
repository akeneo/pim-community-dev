import {useState} from 'react';
import {AttributeOption} from '../models/AttributeOption';
import {useInfiniteAttributeOptions} from './useInfiniteAttributeOptions';

type Cache = {
    [key: string]: AttributeOption;
};

type Result = {
    isLoading: boolean;
    isError: boolean;
    data: AttributeOption[] | undefined;
    error: Error | null;
};

const LIMIT = 20;

export const useAttributeOptionsByCodes = (attribute: string, codes: string[], locale = 'en_US'): Result => {
    const [cache, setCache] = useState<Cache>({});

    const cachedCodes = Object.keys(cache);
    const unknownCodes = codes.filter(code => cachedCodes.indexOf(code) === -1);
    const slicedUnknownCodes = unknownCodes.slice(0, LIMIT);

    const {
        data: options,
        isLoading,
        isError,
        error,
    } = useInfiniteAttributeOptions({
        attribute: attribute,
        locale: locale,
        codes: slicedUnknownCodes,
        limit: LIMIT,
        enabled: slicedUnknownCodes.length > 0,
    });

    if (options !== undefined) {
        const newOptions = options
            .filter(option => !cachedCodes.includes(option.code))
            .reduce(
                (list, option) => ({
                    ...list,
                    [option.code]: option,
                }),
                {}
            );

        if (Object.keys(newOptions).length > 0) {
            setCache(cache => ({
                ...cache,
                ...newOptions,
            }));
        }
    }

    let attributeOptions: AttributeOption[] = Object.values(cache).filter(option => codes.includes(option.code));

    if (!isLoading) {
        const attributeOptionCodes = attributeOptions.map(attributeOption => attributeOption.code);

        const removedAttributeOptionCodes = codes.filter(code => !attributeOptionCodes.includes(code));

        attributeOptions = [
            ...attributeOptions,
            ...removedAttributeOptionCodes.map(code => ({code: code, label: `[${code}]`})),
        ];
    }

    return {
        isLoading: isLoading,
        isError: isError,
        data: attributeOptions,
        error: error,
    };
};
