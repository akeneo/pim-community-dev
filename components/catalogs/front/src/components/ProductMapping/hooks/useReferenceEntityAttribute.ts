import {useQuery} from 'react-query';
import {ReferenceEntityAttribute} from '../models/ReferenceEntityAttribute';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: ReferenceEntityAttribute | undefined;
    error: Error;
};

export const useReferenceEntityAttribute = (identifier: string): Result => {
    return useQuery<ReferenceEntityAttribute, Error, ReferenceEntityAttribute>(
        ['reference_entity_attribute', identifier],
        async () => {
            if ('' === identifier) {
                return undefined;
            }

            const response = await fetch(`/rest/catalogs/reference-entity-attributes/${identifier}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return await response.json();
        }
    );
};
