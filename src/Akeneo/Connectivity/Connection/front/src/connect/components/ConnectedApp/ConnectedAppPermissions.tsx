import React, {FC, useEffect, useState} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {PermissionsForm} from '../PermissionsForm';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppPermissions: FC<Props> = ({connectedApp}) => {
    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => {
            Promise.all(providers.map(provider => provider.loadPermissions(connectedApp.user_group_name))).then(
                providersPermissions => {
                    providers.map((provider, index) => {
                        setPermissions((permissions: PermissionsByProviderKey) => ({
                            ...permissions,
                            [provider.key]: providersPermissions[index],
                        }));
                    });

                    setProviders(providers);
                }
            );
        });
    }, []);

    return (
        <>
            {providers.map(provider => (
                <PermissionsForm
                    key={provider.key}
                    provider={provider}
                    setPermissions={setPermissions}
                    permissions={permissions[provider.key]}
                />
            ))}
        </>
    );
};
