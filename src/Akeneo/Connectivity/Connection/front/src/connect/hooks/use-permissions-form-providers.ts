import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';

type ProvidersAndSavedPermissions = [
    PermissionFormProvider<any>[] | null,
    PermissionsByProviderKey,
    Dispatch<SetStateAction<PermissionsByProviderKey>>
];

const usePermissionsFormProviders = (userGroupName: string): ProvidersAndSavedPermissions => {
    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[] | null>(null);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        (async () => {
            const providers = await permissionFormRegistry.all();

            for (const provider of providers) {
                try {
                    const providersPermissions = await provider.loadPermissions(userGroupName);
                    setPermissions((permissions: PermissionsByProviderKey) => ({
                        ...permissions,
                        [provider.key]: providersPermissions,
                    }));
                } catch {
                    // @todo set permissions to false and display form in readonly
                }
            }

            setProviders(providers);
        })();
    }, [userGroupName]);

    return [providers, permissions, setPermissions];
};

export default usePermissionsFormProviders;
