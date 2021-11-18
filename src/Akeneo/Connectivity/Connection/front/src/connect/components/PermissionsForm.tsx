import React, {FC} from 'react';
import styled from 'styled-components';
import {PermissionFormProvider} from '../../shared/permission-form-registry';

const FormContainer = styled.div`
    padding-bottom: 10px;
`;

const PermissionFormWidget = styled.div`
    max-width: 460px;
`;

type PermissionsFormState = any;

type PermissionsFormProps<T> = {
    provider: PermissionFormProvider<T>;
    onPermissionsChange: (state: T) => void;
    permissions: T | undefined;
    readOnly: boolean | undefined;
};

export const PermissionsForm: FC<PermissionsFormProps<PermissionsFormState>> = React.memo(
    ({provider, onPermissionsChange, permissions, readOnly}) => {
        return <FormContainer>{provider.renderForm(onPermissionsChange, permissions, readOnly)}</FormContainer>;
    }
);
