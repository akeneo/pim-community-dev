import {useMemo} from 'react';
import {useQuery} from '../fetch';

type Result = Array<{code: string; label: string; region: string; language: string}>;

export type Locale = {
    code: string;
    language: string;
};

const useLocale = () => {
    const {loading, data} = useQuery<Result>('pim_enrich_locale_rest_index', {});

    const locales = useMemo<Locale[]>(() => {
        return (data || []).map(error => ({
            code: error.code,
            language: error.language,
        }));
    }, [data]);

    return {loading, locales};
};

export {useLocale};
