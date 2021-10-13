import React, {FC} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {PermissionFormProvider} from '../../../shared/permission-form-registry';
import {PermissionsForm} from '../PermissionsForm';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';

type Props = {
    connectedApp: ConnectedApp;
    providers: PermissionFormProvider<any>[];
    setPermissions: (state: any) => void;
    permissions: PermissionsByProviderKey;
};

export const ConnectedAppPermissions: FC<Props> = ({connectedApp, providers, setPermissions, permissions}) => {
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
