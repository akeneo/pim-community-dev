import {useQuery, useQueryClient} from 'react-query';
import {ReferenceEntityAttribute} from '../models/ReferenceEntityAttribute';
import {Target} from '../models/Target';

type Data = ReferenceEntityAttribute[];
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useReferenceEntityAttributes = (referenceEntityIdentifier: string, target: Target): Result => {
    const queryClient = useQueryClient();

    return useQuery<Data, Error, Data>(
        [
            'referenceEntityAttributes',
            {
                referenceEntityIdentifier: referenceEntityIdentifier,
                targetType: target.type,
                targetFormat: target.format || '',
            },
        ],
        async () => {
            const queryParameters = new URLSearchParams({
                referenceEntityIdentifier: referenceEntityIdentifier,
                targetType: target.type,
                targetFormat: target.format || '',
            }).toString();

            const response = await fetch(
                '/rest/catalogs/reference-entity-attributes-by-target-type-and-target-format?' + queryParameters,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            const referenceEntityAttributes: ReferenceEntityAttribute[] = await response.json();

            Object.entries(referenceEntityAttributes).forEach(([, referenceEntityAttribute]) =>
                queryClient.setQueryData(
                    ['referenceEntityAttribute', referenceEntityAttribute.identifier],
                    referenceEntityAttribute
                )
            );

            return referenceEntityAttributes;
        }
    );
};
