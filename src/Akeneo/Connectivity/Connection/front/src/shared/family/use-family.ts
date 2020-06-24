import {useMemo} from 'react';
import {useQuery} from '../fetch';

type Result = Array<{
    code: string;
    labels: {[locale: string]: string};
}>;

export type Family = {
    code: string;
    label: string;
};

const useFamily = (locale: string) => {
    const {loading, data} = useQuery<Result>('pim_enrich_family_rest_index', {});
    const localeUs = 'en_US';

    const families = useMemo<Family[]>(() => {
        return Object.entries(data || {}).map(error => ({
            code: error[0],
            label: 'string' === typeof error[1].labels[locale] ? error[1].labels[locale] : error[1].labels[localeUs],
        }));
    }, [data, locale]);

    return {loading, families};
};

export {useFamily};
