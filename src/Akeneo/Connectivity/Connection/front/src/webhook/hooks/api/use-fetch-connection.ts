import {useQuery} from '../../../shared/fetch';

type Connection = {
    code: string;
    label: string;
    image: string | null;
};

export function useFetchConnection(code: string) {
    return useQuery<Connection>('akeneo_connectivity_connection_rest_get', {code});
}
