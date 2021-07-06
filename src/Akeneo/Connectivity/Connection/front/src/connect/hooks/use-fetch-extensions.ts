import {useQuery} from '../../shared/fetch';
import {Extension} from '../../model/extension';

type Extensions = {
    total: number;
    extensions: Extension[];
};

export function useFetchExtensions() {
    const {loading, data} = useQuery<Extensions>('akeneo_connectivity_connection_marketplace_rest_get_all_extensions');

    return {extensions: data};
}
