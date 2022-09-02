import {useCallback, useState} from 'react';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useTranslate} from '../../shared/translate';
import {RejectReason, useConfirmAuthorization} from './use-confirm-authorization';
import {PermissionFormProvider} from '../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';

type Result = {
    confirm: () => Promise<void>;
    processing: boolean;
};

export const useConfirmHandler = (
    clientId: string,
    providers: PermissionFormProvider<any>[],
    permissions: PermissionsByProviderKey
): Result => {
    const notify = useNotify();
    const translate = useTranslate();
    const confirmAuthorization = useConfirmAuthorization(clientId);
    const [processing, setProcessing] = useState<boolean>(false);

    const notifyPermissionProviderError = useCallback(
        (entity: string): void => {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.apps.flash.permissions_error.description'),
                {
                    titleMessage: translate(
                        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title',
                        {
                            entity: entity,
                        }
                    ),
                }
            );
        },
        [notify, translate]
    );

    const handleConfirm = useCallback(async () => {
        let userGroup;
        let redirectUrl;

        setProcessing(true);

        try {
            ({userGroup, redirectUrl} = await confirmAuthorization());
        } catch (e) {
            const {status, errors} = e as RejectReason;
            if (400 <= status && status < 500) {
                errors.map(error => notify(NotificationLevel.ERROR, translate(error.message)));
                setProcessing(false);
                return;
            }
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.apps.wizard.flash.error')
            );
            setProcessing(false);
            return;
        }

        for (const provider of providers) {
            if (permissions[provider.key]) {
                try {
                    await provider.save(userGroup, permissions[provider.key]);
                } catch {
                    notifyPermissionProviderError(provider.label);
                }
            }
        }

        notify(
            NotificationLevel.SUCCESS,
            translate('akeneo_connectivity.connection.connect.apps.wizard.flash.success')
        );

        window.location.assign(redirectUrl);
    }, [confirmAuthorization, notify, translate, providers, permissions, notifyPermissionProviderError]);

    return {
        confirm: handleConfirm,
        processing: processing,
    };
};
