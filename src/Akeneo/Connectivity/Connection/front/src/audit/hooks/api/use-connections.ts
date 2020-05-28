import {Connection} from '../../../model/connection';
import {useQuery} from '../../../shared/fetch';

const useConnections = () => {
    const {loading, data} = useQuery<Connection[]>('akeneo_connectivity_connection_rest_list');

    return {loading, connections: data};
};

export {useConnections};
