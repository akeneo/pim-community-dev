import React, {FC, useCallback} from 'react';
import styled from 'styled-components';
import {PermissionsByProviderKey} from '../../model/Apps/permissions-by-provider-key';

const FormContainer = styled.div`
    padding-bottom: 10px;
`;

export const PermissionsForm: FC<RowProps> = React.memo(({provider, setPermissions, permissions}) => {
    const handleChange = useCallback(
        (state: any) => {
            setPermissions((permissions: PermissionsByProviderKey) => ({...permissions, [provider.key]: state}));
        },
        [setPermissions]
    );

    return <FormContainer>{provider.renderForm(handleChange, permissions)}</FormContainer>;
});
