import {useContext} from 'react';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {TranslateContext} from '../../shared/translate';

interface ResultError {
    message: string;
}

export const useDeleteApp = (code: string) => {
    const url = useRoute('akeneo_apps_delete_rest', {code});
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    return async () => {
        const result = await fetchResult<undefined, ResultError>(url, {
            method: 'DELETE',
        });

        if (isErr(result)) {
            notify(NotificationLevel.ERROR, result.error.message);

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('akeneo_apps.delete_app.flash.success'));

        return result;
    };
};
