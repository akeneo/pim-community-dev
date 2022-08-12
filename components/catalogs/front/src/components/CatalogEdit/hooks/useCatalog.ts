import {useCatalogQuery} from '../../../hooks/useCatalogQuery';
import {Catalog} from '../../../models/Catalog';

type Data = Catalog;
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalog = (id: string): Result => {
    return useCatalogQuery(id);
};
