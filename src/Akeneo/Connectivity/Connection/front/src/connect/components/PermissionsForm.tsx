import React, {FC, useCallback} from 'react';
import styled from 'styled-components';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';
import {PermissionFormProvider} from '../../shared/permission-form-registry';

const FormContainer = styled.div`
    padding-bottom: 10px;
`;

type PermissionsFormProps = {
    provider: PermissionFormProvider<any>;
    setPermissions: (state: any) => void;
    permissions: PermissionsByProviderKey | undefined;
};

export const PermissionsForm: FC<PermissionsFormProps> = React.memo(({provider, setPermissions, permissions}) => {
    const handleChange = useCallback(
        (state: any) => {
            setPermissions((permissions: PermissionsByProviderKey) => ({...permissions, [provider.key]: state}));
        },
        [setPermissions]
    );

    return <FormContainer>{provider.renderForm(handleChange, permissions)}</FormContainer>;
});
