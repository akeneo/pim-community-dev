import {Helper, Link} from 'akeneo-design-system';
import React, {useEffect, useMemo, useState} from 'react';
import {FormGroup, Select2, Select2Configuration} from '../../../common';
import {Translate} from '../../../shared/translate';
import {useFetchUserRoles, UserRole} from '../../api-hooks/use-fetch-user-roles';

type Props = {
    userRoleId: string;
    onChange: (userRoleId: string) => void;
};

export const UserRoleSelect = ({userRoleId, onChange}: Props) => {
    const fetchUserRoles = useFetchUserRoles();
    const [userRoles, setUserRoles] = useState<UserRole[]>([]);
    const [selectedUserRole, setSelectedUserRole] = useState<UserRole>();

    useEffect(() => {
        fetchUserRoles().then(setUserRoles);
    }, [fetchUserRoles]);

    useEffect(() => {
        setSelectedUserRole(userRoles.find(userRole => userRole.id === userRoleId));
    }, [userRoles, userRoleId]);

    const handleUserRoleChange = (selectedUserRoleId?: string) => {
        if (!selectedUserRoleId) {
            return;
        }
        setSelectedUserRole(userRoles.find(userRole => userRole.id === selectedUserRoleId));
        onChange(selectedUserRoleId);
    };

    const configuration: Select2Configuration = useMemo(
        () => ({
            data: userRoles.map(userRole => ({id: userRole.id, text: userRole.label})),
        }),
        [userRoles]
    );

    if (!selectedUserRole) {
        return null;
    }

    return (
        <FormGroup
            label='akeneo_connectivity.connection.connection.user_role_id'
            helpers={[
                selectedUserRole.isDefault && (
                    <Helper inline level='warning'>
                        <Translate
                            id='akeneo_connectivity.connection.edit_connection.permissions.user_role_helper.message'
                            placeholders={{role: selectedUserRole.label}}
                        />
                        &nbsp;
                        <Link
                            href='https://help.akeneo.com/pim/articles/manage-your-connections.html#set-the-permissions'
                            target='_blank'
                        >
                            <Translate id='akeneo_connectivity.connection.edit_connection.permissions.user_role_helper.link' />
                        </Link>
                    </Helper>
                ),
            ]}
        >
            <Select2 configuration={configuration} value={selectedUserRole.id} onChange={handleUserRoleChange} />
        </FormGroup>
    );
};
