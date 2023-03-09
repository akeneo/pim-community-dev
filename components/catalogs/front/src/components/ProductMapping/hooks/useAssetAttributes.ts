import {useQuery, useQueryClient} from 'react-query';
import {AssetAttribute} from '../models/AssetAttribute';
import {Target} from '../models/Target';

type Data = AssetAttribute[];
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useAssetAttributes = (assetFamilyIdentifier: string, target: Target): Result => {
    const queryClient = useQueryClient();

    return useQuery<Data, Error, Data>(
        ['assetAttributes', {assetFamilyIdentifier: assetFamilyIdentifier, targetType: target.type, targetFormat: target.format || ''}],
        async () => {
            const queryParameters = new URLSearchParams({
                assetFamilyIdentifier: assetFamilyIdentifier,
                targetType: target.type,
                targetFormat: target.format || '',
            }).toString();

            const response = await fetch(
                '/rest/catalogs/asset-attributes-by-target-type-and-target-format?' + queryParameters,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            const assetAttributes: AssetAttribute[] = await response.json();

            Object.entries(assetAttributes).forEach(([, assetAttribute]) =>
                queryClient.setQueryData(['assetAttribute', assetAttribute.identifier], assetAttribute)
            );

            return assetAttributes;
        }
    );
};
