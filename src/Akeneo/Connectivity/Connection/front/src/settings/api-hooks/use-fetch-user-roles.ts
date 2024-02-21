import {useRoute} from '../../shared/router';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {useCallback} from 'react';

const DEFAULT_USER_ROLE = 'ROLE_USER';

type ResultValue = Array<{id: number; role: string; label: string}>;

export type UserRole = {id: string; label: string; isDefault: boolean};

export const useFetchUserRoles = () => {
    const route = useRoute('pim_user_user_role_rest_index');

    return useCallback(
        (): Promise<UserRole[]> =>
            fetchResult<ResultValue, unknown>(route).then(result => {
                if (isErr(result)) {
                    throw new Error();
                }

                return result.value.map(userRole => ({
                    id: userRole.id.toString(),
                    label: userRole.label,
                    isDefault: DEFAULT_USER_ROLE === userRole.role,
                }));
            }),
        [route]
    );
};
