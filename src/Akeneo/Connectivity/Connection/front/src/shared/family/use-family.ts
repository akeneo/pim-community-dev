import {useMemo} from 'react';
import {useQuery} from '../fetch';

type Result = {
    [code: string]: {
        code: string;
        labels: {[locale: string]: string};
    };
};

export type Family = {
    code: string;
    label: string;
};

const useFamily = (locale: string) => {
    const {loading, data} = useQuery<Result>('pim_enrich_family_rest_index', {});
    const families = useMemo<Family[]>(() => {
        return Object.values(data || {}).map(({code, labels}) => ({
            code,
            label: labels[locale] || `[${code}]`,
        }));
    }, [data, locale]);

    return {loading, families};
};

export {useFamily};
