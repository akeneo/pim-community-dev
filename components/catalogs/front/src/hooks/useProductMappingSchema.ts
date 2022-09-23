import {useQuery} from 'react-query';

type Data = null;
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useProductMappingSchema = (catalogId: string): Result => {
    return useQuery<Data, Error, Data>(['catalog-mapping-requirements', catalogId], () => {
        return null;
    });
};
