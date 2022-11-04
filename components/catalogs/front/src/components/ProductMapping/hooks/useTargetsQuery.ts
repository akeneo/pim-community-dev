import {useQuery} from 'react-query';
import {Target} from '../models/Target';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Target[] | undefined;
    error: Error;
};

export const useTargetsQuery = (catalogId: string): Result => {
    return useQuery<Target[], Error, Target[]>(['targets', catalogId], async () => {
        const response = await fetch(`/rest/catalogs/targets/${catalogId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
