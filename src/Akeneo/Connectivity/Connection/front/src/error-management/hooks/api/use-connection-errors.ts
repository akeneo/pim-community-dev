import {useMemo} from 'react';
import {useQuery} from '../../../shared/fetch';

type ConnectionError = {id: number; timestamp: number; content: {message: string; property?: string}};

const useConnectionErrors = (connectionCode: string) => {
    const {loading, data} = useQuery<Array<{date_time: string; content: {message: string}}>>(
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
