import {useQuery} from 'react-query';
import {AssetAttribute} from '../models/AssetAttribute';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: AssetAttribute | undefined;
    error: Error;
};

export const useAssetAttribute = (identifier: string): Result => {
    return useQuery<AssetAttribute, Error, AssetAttribute>(['asset_attribute', identifier], async () => {
        if ('' === identifier) {
            return undefined;
        }

        const response = await fetch(`/rest/catalogs/asset-attributes/${identifier}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
