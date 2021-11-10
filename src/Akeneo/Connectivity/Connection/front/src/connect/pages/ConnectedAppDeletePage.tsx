import React from 'react';
import {useHistory, useParams} from 'react-router';
import {GreyButton, ImportantButton, Modal} from '../../common';
import styled from '../../common/styled-with-theme';
import {isOk} from '../../shared/fetch-result/result';
import {Translate} from '../../shared/translate';
import {connectionDeleted} from '../../settings/actions/connections-actions';
// import {useDeleteConnection} from '../../settings/api-hooks/use-delete-connection';
// import {useConnectionsDispatch} from '../../settings/connections-context';

export const ConnectedAppDeletePage = () => {
    const history = useHistory();

    const {connectionCode} = useParams<{connectionCode: string}>();
    // const connection = useConnection('foo');
    // const deleteConnection = useDeleteConnection(connectionCode);
    // const dispatch = useConnectionsDispatch();

    const handleClick = async () => {
        // const result = await deleteConnection();

        // if (isOk(result)) {
        //     dispatch(connectionDeleted(connectionCode));

        //     history.push('/connect/connection-settings');
        // }
    };

    const handleCancel = () => history.push(`/connect/connected-apps/${connectionCode}`);

    const description = (
        <>
            <Translate id='akeneo_connectivity.app.delete_app.description' />
            &nbsp;
            <Link
                href='https://help.akeneo.com/pim/articles/manage-your-apps.html#delete-an-app'
                target='_blank'
            >
                <Translate id='akeneo_connectivity.app.delete_app.link' />
            </Link>
        </>
    );

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.app.apps' />}
            title={<Translate id='akeneo_connectivity.app.delete_app.title' />}
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
