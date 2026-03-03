import {Connection} from '../../../model/connection';
import {useQuery} from '../../../shared/fetch';

const useConnections = () => {
    const {loading, data} = useQuery<Connection[]>('akeneo_connectivity_connection_rest_list', {
        search: JSON.stringify({
            types: ['default', 'app'],
        }),
    });

    return {loading, connections: data};
};

export {useConnections};
