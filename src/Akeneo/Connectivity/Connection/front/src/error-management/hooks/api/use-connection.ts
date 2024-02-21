import {FlowType} from '../../../model/flow-type.enum';
import {useQuery} from '../../../shared/fetch';

type Connection = {
    label: string;
    flow_type: FlowType;
    auditable: boolean;
};

const useConnection = (connectionCode: string) => {
    const {loading, data} = useQuery<Connection>('akeneo_connectivity_connection_rest_get', {
        code: connectionCode,
    });

    return {loading, connection: data};
};

export {useConnection};
