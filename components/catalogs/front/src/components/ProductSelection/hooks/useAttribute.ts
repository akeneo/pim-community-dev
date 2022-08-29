import {useQuery} from 'react-query';
import {Attribute} from '../models/Attribute';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Attribute | undefined;
    error: Error;
};

export const useAttribute = (code: string): Result => {
    return useQuery<Attribute, Error, Attribute>(['attribute', code], async () => {
        const response = await fetch(`/rest/catalogs/attributes/${code}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
