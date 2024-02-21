import {useCallback} from 'react';
import {Connection} from '../../model/connection';
import {ConnectionCredentials} from '../../model/connection-credentials';
import {ConnectionUserPermissions} from '../../model/connection-user-permissions';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isErr, ok} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';

type ResultValue = {
    code: string;
    label: string;
    flow_type: FlowType;
    image: string | null;
    client_id: string;
    secret: string;
    username: string;
    password: string;
    user_role_id: string;
    user_group_id: string | null;
    auditable: boolean;
};

export const useFetchConnection = (code: string) => {
    const url = useRoute('akeneo_connectivity_connection_rest_get', {code});

    return useCallback(async () => {
        const result = await fetchResult<ResultValue, unknown>(url);
        if (isErr(result)) {
            return result;
        }

        const connection: Connection & ConnectionCredentials & ConnectionUserPermissions = {
            ...result.value,
            flowType: result.value.flow_type,
            clientId: result.value.client_id,
            userRoleId: result.value.user_role_id,
            userGroupId: result.value.user_group_id,
            auditable: result.value.auditable,
        };

        return ok(connection);
    }, [url]);
};
