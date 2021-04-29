import React from 'react';
import {useHistory, useParams} from 'react-router';
import {GreyButton, ImportantButton, Modal} from '../../common';
import styled from '../../common/styled-with-theme';
import {isOk} from '../../shared/fetch-result/result';
import {Translate} from '../../shared/translate';
import {connectionDeleted} from '../actions/connections-actions';
import {useDeleteConnection} from '../api-hooks/use-delete-connection';
import {useConnectionsDispatch} from '../connections-context';

export const DeleteConnection = () => {
    const history = useHistory();

    const {code} = useParams<{code: string}>();
    const deleteConnection = useDeleteConnection(code);
    const dispatch = useConnectionsDispatch();

    const handleClick = async () => {
        const result = await deleteConnection();

        if (isOk(result)) {
            dispatch(connectionDeleted(code));

            history.push('/connect/connection-settings');
        }
    };

    const handleCancel = () => history.push(`/connect/connection-settings/${code}/edit`);

    const description = (
        <>
            <Translate id='akeneo_connectivity.connection.delete_connection.description' />
            &nbsp;
            <Link
                href='https://help.akeneo.com/pim/articles/manage-your-connections.html#delete-a-connection'
                target='_blank'
            >
                <Translate id='akeneo_connectivity.connection.delete_connection.link' />
            </Link>
        </>
    );

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.connection.connections' />}
            title={<Translate id='akeneo_connectivity.connection.delete_connection.title' />}
            description={description}
            onCancel={handleCancel}
        >
            <GreyButton onClick={handleCancel} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.cancel' />
            </GreyButton>
            <ImportantButton onClick={handleClick} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.delete' />
            </ImportantButton>
        </Modal>
    );
};

const Link = styled.a`
    color: ${({theme}) => theme.color.blue100};
    text-decoration: underline;
`;
