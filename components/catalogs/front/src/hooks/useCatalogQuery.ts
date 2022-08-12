import {useQuery} from 'react-query';
import {getCatalog} from '../api/getCatalog';
import {Catalog} from '../models/Catalog';

type Data = Catalog;
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalogQuery = (id: string): Result => {
    return useQuery<Data, Error, Data>(['catalog', id], async () => await getCatalog(id));
};
