import {useQuery} from 'react-query';
import {Locale} from '../models/Locale';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Locale[] | undefined;
    error: ResultError;
};

export const useLocalesByCodes = (codes: string[] | undefined): Result => {
    return useQuery<Locale[], ResultError, Locale[]>(['locales', codes?.sort().join('')], async () => {
        if (undefined === codes || codes.length === 0) {
            return [];
        }
        const concatCodes = codes.join(',');

        const response = await fetch(`/rest/catalogs/locales?codes=${concatCodes}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
