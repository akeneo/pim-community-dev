import React, {FC} from 'react';
import styled from 'styled-components';
import {PermissionFormProvider} from '../../shared/permission-form-registry';
import {PermissionFormReducer} from '../../../../workspaces/permission-form';

const FormContainer = styled.div`
    padding-bottom: 10px;
`;

type PermissionsFormProps<T> = {
    provider: PermissionFormProvider<T>;
    onPermissionsChange: (state: T) => void;
    permissions: T | undefined;
    readOnly: boolean | undefined;
};

export const PermissionsForm: FC<PermissionsFormProps<PermissionFormReducer.State>> = React.memo(
    ({provider, onPermissionsChange, permissions, readOnly}) => {
        return <FormContainer>{provider.renderForm(onPermissionsChange, permissions, readOnly)}</FormContainer>;
    }
);
