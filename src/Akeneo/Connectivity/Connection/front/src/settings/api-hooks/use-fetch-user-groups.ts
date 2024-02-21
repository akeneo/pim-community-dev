import {useCallback} from 'react';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';

type ResultValue = Array<{name: string; meta: {id: number; default: boolean}}>;

export type UserGroup = {id: string; label: string; isDefault: boolean};

export const useFetchUserGroups = () => {
    const route = useRoute('pim_user_user_group_rest_index');

    return useCallback(
        (): Promise<UserGroup[]> =>
            fetchResult<ResultValue, unknown>(route).then(result => {
                if (isErr(result)) {
                    throw new Error();
                }

                return result.value.map(userGroup => ({
                    id: userGroup.meta.id.toString(),
                    label: userGroup.name,
                    isDefault: userGroup.meta.default,
                }));
            }),
        [route]
    );
};
