import {useMemo} from 'react';
import {useQuery} from '../fetch';

type Result = Array<{code: string; labels: {[locale: string]: string}}>;

export type Channel = {
    code: string;
    label: string;
};

const useChannel = (locale: string) => {
    const {loading, data} = useQuery<Result>('pim_enrich_channel_rest_index', {});
    const channels = useMemo<Channel[]>(() => {
        return (data || []).map(({code, labels}) => ({
            code,
            label: labels[locale] || `[${code}]`,
        }));
    }, [data, locale]);

    return {loading, channels};
};

export {useChannel};
