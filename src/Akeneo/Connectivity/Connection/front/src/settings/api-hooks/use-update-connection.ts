import {useContext} from 'react';
import {Connection} from '../../model/connection';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {TranslateContext} from '../../shared/translate';
import {ConnectionUserPermissions} from '../../model/connection-user-permissions';

type RequestData = Connection & ConnectionUserPermissions;

type ResultError = {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
};

export const useUpdateConnection = (code: string) => {
    const url = useRoute('akeneo_connectivity_connection_rest_update', {code});
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    return async (data: RequestData) => {
        const result = await fetchResult<never, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify({
                code: data.code,
                label: data.label,
                flow_type: data.flowType,
                image: data.image,
                user_role_id: data.userRoleId,
                user_group_id: data.userGroupId,
            }),
        });
        if (isErr(result)) {
            if (result.error.errors) {
                result.error.errors.forEach(({reason}) => notify(NotificationLevel.ERROR, translate(reason)));
            } else {
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.edit_connection.flash.error')
                );
            }

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('akeneo_connectivity.connection.edit_connection.flash.success'));

        return result;
    };
};
