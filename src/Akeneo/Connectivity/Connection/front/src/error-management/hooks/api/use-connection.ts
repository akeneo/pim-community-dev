import {useQuery} from '../../services/fetch';

type Connection = {label: string};

const useConnection = (connectionCode: string) => {
    const {loading, data} = useQuery<Connection>('akeneo_connectivity_connection_rest_get', {
        code: connectionCode,
    });

    return {loading, connection: data};
};

export {useConnection};
