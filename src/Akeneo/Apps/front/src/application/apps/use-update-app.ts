import {useContext} from 'react';
import {App} from '../../domain/apps/app.interface';
import {fetch} from '../shared/fetch';
import {isErr} from '../shared/fetch/result';
import {NotificationLevel, useNotify} from '../shared/notify';
import {useRoute} from '../shared/router';
import {TranslateContext} from '../shared/translate';

interface ResultError {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
}

export const useUpdateApp = (code: string) => {
    const url = useRoute('akeneo_apps_update_rest', {code});
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    return async (app: App) => {
        const result = await fetch<undefined, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify({
                code: app.code,
                label: app.label,
                flow_type: app.flowType,
            }),
        });
        if (isErr(result)) {
            if (!result.error.errors) {
                notify(NotificationLevel.ERROR, translate('pim_apps.edit_app.flash.error'));

                return result;
            }

            result.error.errors
                .filter(({name}) => name !== 'label')
                .forEach(({reason}) => notify(NotificationLevel.ERROR, translate(reason)));

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('pim_apps.edit_app.flash.success'));

        return result;
    };
};
