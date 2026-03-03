import {Helper, Link, SelectInput} from 'akeneo-design-system';
import React, {useEffect, useState} from 'react';
import {FormGroup} from '../../../common';
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

    const handleUserRoleChange = (selectedUserRoleId: string | null) => {
        if (null === selectedUserRoleId) {
            return;
        }
        setSelectedUserRole(userRoles.find(userRole => userRole.id === selectedUserRoleId));
        onChange(selectedUserRoleId);
    };

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
            controlId='user_role'
        >
            <SelectInput
                value={selectedUserRole.id}
                onChange={handleUserRoleChange}
                clearable={false}
                emptyResultLabel=''
                openLabel=''
                id='user_role'
            >
                {userRoles.map(userRole => (
                    <SelectInput.Option key={userRole.id} value={userRole.id}>
                        {userRole.label}
                    </SelectInput.Option>
                ))}
            </SelectInput>
        </FormGroup>
    );
};
