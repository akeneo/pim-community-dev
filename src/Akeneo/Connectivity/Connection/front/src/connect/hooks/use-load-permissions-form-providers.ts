import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';

type Return = [
    PermissionFormProvider<any>[] | null,
    PermissionsByProviderKey,
    Dispatch<SetStateAction<PermissionsByProviderKey>>,
];

const useLoadPermissionsFormProviders = (userGroupName: string): Return => {
    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[] | null>(null);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => {
            Promise.all(providers.map(provider => provider.loadPermissions(userGroupName))).then(
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
    }, [userGroupName]);

    return [providers, permissions, setPermissions];
};

export default useLoadPermissionsFormProviders;
