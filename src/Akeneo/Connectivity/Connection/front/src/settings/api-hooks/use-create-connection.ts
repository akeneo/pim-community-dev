import {useContext} from 'react';
import {Connection} from '../../model/connection';
import {ConnectionCredentials} from '../../model/connection-credentials';
import {ConnectionUserPermissions} from '../../model/connection-user-permissions';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isErr, ok} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {TranslateContext} from '../../shared/translate';

interface RequestData {
    code: string;
    label: string;
    flow_type: FlowType;
}

interface ResultValue {
    code: string;
    label: string;
    flow_type: FlowType;
    image: string | null;
    client_id: string;
    secret: string;
    username: string;
    password: string;
    user_role_id: string;
    user_group_id: string;
}

interface ResultError {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
}

export const useCreateConnection = () => {
    const url = useRoute('akeneo_connectivity_connection_rest_create');
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    return async (data: RequestData) => {
        const result = await fetchResult<ResultValue, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify(data),
        });

        if (isErr(result)) {
            if (undefined === result.error.errors) {
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.create_connection.flash.error')
                );
            }

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('akeneo_connectivity.connection.create_connection.flash.success'));

        const connection: Connection & ConnectionCredentials & ConnectionUserPermissions = {
            ...result.value,
            flowType: result.value.flow_type,
            clientId: result.value.client_id,
            userRoleId: result.value.user_role_id,
            userGroupId: result.value.user_group_id,
        };

        return ok(connection);
    };
};
