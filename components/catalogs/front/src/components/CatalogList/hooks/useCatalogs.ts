import {useQuery} from 'react-query';
import {getCatalogsByOwner} from '../../../api/getCatalogsByOwner';
import {Catalog} from '../../../models/Catalog';

type Data = Catalog[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCatalogs = (owner: string): Result => {
    return useQuery<Data, ResultError, Data>(['catalogs_list', owner], async () => await getCatalogsByOwner(owner));
};
