import {useCatalogQuery} from './useCatalogQuery';

type Data = {
    id: string;
    name: string;
    enabled: boolean;
    owner_username: string;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useCatalog = (catalogId: string): Result => {
    return useCatalogQuery(catalogId);
};
