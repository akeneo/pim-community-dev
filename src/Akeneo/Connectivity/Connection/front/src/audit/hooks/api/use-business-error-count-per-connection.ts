import {useQuery} from '../../../shared/fetch';

export const useBusinessErrorCountPerConnection = () => {
    const {loading, data} = useQuery<{[connectionCode: string]: number}>(
        'akeneo_connectivity_connection_audit_rest_error_count_per_connection',
        {
            error_type: 'business',
        }
    );

    const errorCountPerConnection = Object.entries(data || {})
        .map(([connectionCode, errorCount]) => ({connectionCode, errorCount}))
        .sort((a, b) => b.errorCount - a.errorCount);

    return {loading, errorCountPerConnection};
};
