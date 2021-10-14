import React, {FC} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {PermissionsForm} from '../PermissionsForm';
import useLoadPermissionsFormProviders from '../../hooks/use-load-permissions-form-providers';

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppPermissions: FC<Props> = ({connectedApp}) => {
    const [providers, permissions, setPermissions] = useLoadPermissionsFormProviders(connectedApp.user_group_name);

    return (
        <>
            {null !== providers &&
                providers.map(provider => (
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
