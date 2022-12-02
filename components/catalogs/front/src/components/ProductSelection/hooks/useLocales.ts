import {useQuery} from 'react-query';
import {Locale} from '../../../models/Locale';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Locale[] | undefined;
    error: ResultError;
};

export const useLocales = (): Result => {
    return useQuery<Locale[], ResultError, Locale[]>('locales', async () => {
        const response = await fetch('/rest/catalogs/locales', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
