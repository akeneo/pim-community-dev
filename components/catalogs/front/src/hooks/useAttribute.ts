import {useQuery} from 'react-query';
import {Attribute} from '../models/Attribute';
import {useSystemAttributes} from './useSystemAttributes';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Attribute | undefined;
    error: Error;
};

export const useAttribute = (code: string): Result => {
    const systemAttributes = useSystemAttributes({target: null, search: null});
    const systemAttribute = systemAttributes.find(systemAttribute => systemAttribute.code === code) ?? null;
    return useQuery<Attribute, Error, Attribute>(['attribute', code], async () => {
        if ('' === code) {
            return undefined;
        }

        if (null !== systemAttribute) {
            return systemAttribute;
        }

        const response = await fetch(`/rest/catalogs/attributes/${code}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
