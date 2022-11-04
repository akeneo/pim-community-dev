import {useTargetsQuery} from './useTargetsQuery';

export const useProductMappingSchemaExist = (catalogId: string): boolean => {
    const {data: targets, isLoading} = useTargetsQuery(catalogId);

    return isLoading === false && Array.isArray(targets);
};
