import React, {FC, SetStateAction, useCallback} from 'react';
import {PermissionFormProvider} from '../../../shared/permission-form-registry';
import {PermissionsForm} from '../PermissionsForm';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';

type Props = {
    providers: PermissionFormProvider<any>[];
    setProviderPermissions: (providerKey: string, providerPermissions: object) => void;
    permissions: PermissionsByProviderKey;
};

export const ConnectedAppPermissions: FC<Props> = ({providers, setProviderPermissions, permissions}) => {
    return (
        <>
            {null !== providers &&
                providers.map(provider => {
                    const readOnly = false === permissions[provider.key];
                    const providerPermissions = false !== permissions[provider.key] ? permissions[provider.key] : undefined;
                    const handlePermissionsChange = useCallback((providerPermissions: object) => {
                        setProviderPermissions(provider.key, providerPermissions);
                    }, [setProviderPermissions, provider.key]);

                    return (
                        <PermissionsForm
                            key={provider.key}
                            provider={provider}
                            onPermissionsChange={handlePermissionsChange}
                            permissions={providerPermissions}
                            readOnly={readOnly}
                        />
                    )
                }
            )}
        </>
    );
};
