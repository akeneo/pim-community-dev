import {useFormikContext} from 'formik';
import React, {FC} from 'react';
import styled from 'styled-components';
import {HelperLink, SmallHelper} from '../../../common';
import {Translate} from '../../../shared/translate';
import {FormValues} from '../../pages/EditConnection';
import {UserGroupSelect} from './UserGroupSelect';
import {UserRoleSelect} from './UserRoleSelect';
import {SectionTitle} from 'akeneo-design-system';

type Props = {
    label: string;
};

export const ConnectionPermissionsForm: FC<Props> = ({label}: Props) => {
    const {values, setFieldValue} = useFormikContext<FormValues>();

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    <Translate id='akeneo_connectivity.connection.edit_connection.permissions.title' />
                </SectionTitle.Title>
            </SectionTitle>
            <SmallHelper>
                <Translate
                    id='akeneo_connectivity.connection.edit_connection.permissions.helper.message'
                    placeholders={{label}}
                />
                &nbsp;
                <HelperLink
                    href='https://help.akeneo.com/pim/articles/manage-your-connections.html#set-the-permissions'
                    target='_blank'
                    rel='noopener noreferrer'
                >
                    <Translate id='akeneo_connectivity.connection.edit_connection.permissions.helper.link' />
                </HelperLink>
            </SmallHelper>

            <Container>
                <UserRoleSelect
                    userRoleId={values.userRoleId}
                    onChange={userRoleId => setFieldValue('userRoleId', userRoleId)}
                />

                <UserGroupSelect
                    userGroupId={values.userGroupId}
                    onChange={userGroupId => setFieldValue('userGroupId', userGroupId)}
                />
            </Container>
        </>
    );
};

const Container = styled.div`
    max-width: 400px;
    padding-top: 1rem;
    width: 100%;
`;
