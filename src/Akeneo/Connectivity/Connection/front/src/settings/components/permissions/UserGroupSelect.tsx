import {Helper, Link} from 'akeneo-design-system';
import React, {useEffect, useMemo, useState} from 'react';
import {FormGroup, Select2, Select2Configuration} from '../../../common';
import {Translate} from '../../../shared/translate';
import {useFetchUserGroups, UserGroup} from '../../api-hooks/use-fetch-user-groups';

const findUserGroup = (userGroups: UserGroup[], userGroupId: string | null) =>
    userGroups.find(userGroup => {
        if (null === userGroupId) {
            return userGroup.isDefault;
        } else {
            return userGroup.id === userGroupId;
        }
    });

type Props = {
    userGroupId: string | null;
    onChange: (userGroupId: string | null) => void;
};

export const UserGroupSelect = ({userGroupId, onChange}: Props) => {
    const fetchUserGroups = useFetchUserGroups();
    const [userGroups, setUserGroups] = useState<UserGroup[]>([]);
    const [selectedUserGroup, setSelectedUserGroup] = useState<UserGroup>();

    useEffect(() => {
        fetchUserGroups().then(setUserGroups);
    }, [fetchUserGroups]);

    useEffect(() => {
        setSelectedUserGroup(findUserGroup(userGroups, userGroupId));
    }, [userGroups, userGroupId]);

    const handleUserGroupChange = (selectedUserGroupId?: string) => {
        setSelectedUserGroup(findUserGroup(userGroups, selectedUserGroupId || null));
        onChange(selectedUserGroupId || null);
    };

    const configuration: Select2Configuration = useMemo(
        () => ({
            data: userGroups
                .filter(({isDefault}) => false === isDefault)
                .map(userGroup => ({id: userGroup.id, text: userGroup.label})),
            allowClear: true,
            placeholder: '<null>',
        }),
        [userGroups]
    );

    if (!selectedUserGroup) {
        return null;
    }

    return (
        <FormGroup
            label='akeneo_connectivity.connection.connection.user_group_id'
            helpers={[
                selectedUserGroup.isDefault && (
                    <Helper inline level='warning'>
                        <Translate id='akeneo_connectivity.connection.edit_connection.permissions.user_group_helper.message' />
                        &nbsp;
                        <Link
                            href='https://help.akeneo.com/pim/articles/manage-your-connections.html#set-the-permissions'
                            target='_blank'
                            rel='noopener noreferrer'
                        >
                            <Translate id='akeneo_connectivity.connection.edit_connection.permissions.user_group_helper.link' />
                        </Link>
                    </Helper>
                ),
            ]}
        >
            <Select2 configuration={configuration} value={selectedUserGroup.id} onChange={handleUserGroupChange} />
        </FormGroup>
    );
};
