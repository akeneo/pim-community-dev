import React, {Dispatch, FC, SetStateAction} from 'react';
import {PermissionFormProvider} from '../../../shared/permission-form-registry';
import {PermissionsForm} from '../PermissionsForm';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';

type Props = {
    providers: PermissionFormProvider<any>[];
    setPermissions: (state: SetStateAction<PermissionsByProviderKey>) => void;
    permissions: PermissionsByProviderKey;
};

export const ConnectedAppPermissions: FC<Props> = ({providers, setPermissions, permissions}) => {
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
