import {useQuery} from 'react-query';
import {Locale} from '../models/Locale';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Locale[] | undefined;
    error: ResultError;
};

export const useLocalesByCodes = (codes: string[]): Result => {
    return useQuery<Locale[], ResultError, Locale[]>(['locales', [...codes].sort().join('')], async () => {

        const concatCodes = codes.join(',');

        if (concatCodes === '') {
            return [];
        }

        const response = await fetch(`/rest/catalogs/locales?codes=${concatCodes}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const locales: Locale[] = await response.json();

        const localeCodes = locales.map(locale => locale.code);

        const deactivatedLocaleCodes = codes.filter(code => !localeCodes.includes(code));

        return [...locales, ...deactivatedLocaleCodes.map(code => ({code: code, label: `[${code}]`}))];
    });
};
