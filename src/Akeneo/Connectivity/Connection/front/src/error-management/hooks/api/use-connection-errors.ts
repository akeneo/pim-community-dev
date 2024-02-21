import {useMemo} from 'react';
import {useQuery} from '../../../shared/fetch';
import {ConnectionError} from '../../model/ConnectionError';

type HrefParameter = {
    type: 'href';
    title: string;
    href: string;
};

type RouteParameter = {
    type: 'route';
    title: string;
    route: string;
    routeParameters: {
        [parameterName: string]: string;
    };
};

type Result = Array<{
    date_time: string;
    content: {
        message: string;
        type: 'violation_error' | 'domain_error';
        message_template?: string;
        message_parameters?: {[key: string]: string};
        product?: {id: number | null; identifier: string; family: string | null; label: string};
        documentation?: Array<{
            message: string;
            parameters: {[needle: string]: HrefParameter | RouteParameter};
            style: 'text' | 'information';
        }>;
        locale?: string | null;
        scope?: string | null;
    } & {[key: string]: unknown};
}>;

const useConnectionErrors = (connectionCode: string) => {
    const {loading, data} = useQuery<Result>(
        'akeneo_connectivity_connection_error_management_rest_get_connection_business_errors',
        {
            connection_code: connectionCode,
        }
    );

    const connectionErrors = useMemo<ConnectionError[]>(() => {
        return (data || []).map((error, index) => ({
            id: index, // Add a unique id to each value.
            timestamp: Date.parse(error.date_time),
            ...error,
        }));
    }, [data]);

    return {loading, connectionErrors};
};

export {useConnectionErrors, ConnectionError};
