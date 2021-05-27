import {useQuery} from '../../shared/fetch';

type UserProfileEntry = {
    code: string;
    label: string;
};
export function useFetchUserProfiles(): UserProfileEntry[] {
    const {loading, data} = useQuery('pim_user_rest_find_all_profiles');console.log(loading, data);
    return loading ? [] : data;
}
