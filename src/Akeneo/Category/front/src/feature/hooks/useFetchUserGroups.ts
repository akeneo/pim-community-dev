import {useRoute} from "@akeneo-pim-community/shared";
import {useCallback} from "react";
import {useQuery} from "react-query";
import {ResponseStatus} from "../models/ResponseStatus";

const USER_GROUPS_FETCH_STALE_TIME = 60 * 60 * 1000;

type ResultError = Error | null;

type Result = {
    status: ResponseStatus;
    data: UserGroup[] | undefined;
    error: ResultError;
};

type UserGroupValue = {
    name: string;
    meta: {
        id: number;
        default: boolean
    }
};

export type UserGroup = {
    id: string;
    label: string;
    isDefault: boolean;
};

export const useFetchUserGroups = (): Result => {
    const url = useRoute('pim_user_user_group_rest_index');

    const fetchUserGroups = useCallback(async () => {
       const response = await fetch(url);

        if (!response.ok) {
            throw new Error();
        }

        const userGroups = await response.json();
        return userGroups.map((userGroup: UserGroupValue) => ({
            id: userGroup.meta.id.toString(),
            label: userGroup.name,
            isDefault: userGroup.meta.default,
        }));
    }, [url]);

    const options = {
        staleTime: USER_GROUPS_FETCH_STALE_TIME,
    };

    return useQuery<UserGroup[], ResultError, UserGroup[]>(['user-groups'], fetchUserGroups, options);
};
