import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useTranslate} from '../../shared/translate';

type ProvidersAndSavedPermissions = [
    PermissionFormProvider<any>[] | null,
    PermissionsByProviderKey,
    Dispatch<SetStateAction<PermissionsByProviderKey>>
];

const usePermissionsFormProviders = (userGroupName: string): ProvidersAndSavedPermissions => {
    const translate = useTranslate();
    const notify = useNotify();
    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[] | null>(null);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    const notifyPermissionProviderError = (entity: string): void => {
        notify(
            NotificationLevel.ERROR,
            translate(
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.load_permissions_error.description'
            ),
            {
                titleMessage: translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.flash.load_permissions_error.title',
                    {
                        entity: entity,
                    }
                ),
            }
        );
    };

    useEffect(() => {
        (async () => {
            const providers = await permissionFormRegistry.all();

            for (const provider of providers) {
                let providerPermissions = false;
                try {
                    providerPermissions = await provider.loadPermissions(userGroupName);
                } catch {
                    notifyPermissionProviderError(provider.label);
                }

                setPermissions((permissions: PermissionsByProviderKey) => ({
                    ...permissions,
                    [provider.key]: providerPermissions,
                }));
            }

            setProviders(providers);
        })();
    }, [userGroupName]);

    return [providers, permissions, setPermissions];
};

export default usePermissionsFormProviders;
