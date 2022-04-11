import React, {FC} from 'react';
import styled from 'styled-components';
import {PermissionFormProvider} from '../../shared/permission-form-registry';

const FormContainer = styled.div`
    padding-bottom: 10px;
`;

type PermissionsFormState = any;

type PermissionsFormProps<T> = {
    provider: PermissionFormProvider<T>;
    onPermissionsChange: (state: T) => void;
    permissions: T | undefined;
    readOnly: boolean | undefined;
    onlyDisplayViewPermissions: boolean | undefined;
};

export const PermissionsForm: FC<PermissionsFormProps<PermissionsFormState>> = React.memo(
    ({provider, onPermissionsChange, permissions, readOnly, onlyDisplayViewPermissions}) => {
        return (
            <FormContainer>
                {provider.renderForm(onPermissionsChange, permissions, readOnly, onlyDisplayViewPermissions)}
            </FormContainer>
        );
    }
);
