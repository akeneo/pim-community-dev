import {Helper, Link, SelectInput} from 'akeneo-design-system';
import React, {FC, useEffect, useState} from 'react';
import {FormGroup} from '../../../common';
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

export const UserGroupSelect: FC<Props> = ({userGroupId, onChange}: Props) => {
    const fetchUserGroups = useFetchUserGroups();
    const [userGroups, setUserGroups] = useState<UserGroup[]>([]);
    const [selectedUserGroup, setSelectedUserGroup] = useState<UserGroup>();

    useEffect(() => {
        fetchUserGroups().then(setUserGroups);
    }, [fetchUserGroups]);

    useEffect(() => {
        setSelectedUserGroup(findUserGroup(userGroups, userGroupId));
    }, [userGroups, userGroupId]);

    const handleUserGroupChange = (selectedUserGroupId: string | null) => {
        setSelectedUserGroup(findUserGroup(userGroups, selectedUserGroupId));
        onChange(selectedUserGroupId);
    };

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
            controlId='user_group'
        >
            <SelectInput
                value={(!selectedUserGroup.isDefault && selectedUserGroup.id) || null}
                onChange={handleUserGroupChange}
                emptyResultLabel=''
                openLabel=''
                id='user_group'
            >
                {userGroups
                    .filter(({isDefault}) => false === isDefault)
                    .map(userGroup => (
                        <SelectInput.Option key={userGroup.id} value={userGroup.id}>
                            {userGroup.label}
                        </SelectInput.Option>
                    ))}
            </SelectInput>
        </FormGroup>
    );
};
