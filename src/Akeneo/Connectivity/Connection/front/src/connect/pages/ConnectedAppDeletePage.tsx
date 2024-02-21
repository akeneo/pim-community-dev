import React, {useCallback} from 'react';
import {useHistory, useParams} from 'react-router';
import {Modal, AppIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useDeleteApp} from '../hooks/use-delete-app';
import {useRouter} from '../../shared/router/use-router';
import {NotificationLevel, useNotify} from '../../shared/notify';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

const Helper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 17px 0 0 0;
`;

export const ConnectedAppDeletePage = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const notify = useNotify();

    const {connectionCode} = useParams<{connectionCode: string}>();
    const deleteApp = useDeleteApp(connectionCode);

    const handleClick = useCallback(async () => {
        try {
            await deleteApp();
            notify(
                NotificationLevel.SUCCESS,
                translate('akeneo_connectivity.connection.connect.connected_apps.delete.flash.success')
            );
            history.push(generateUrl('akeneo_connectivity_connection_connect_connected_apps'));
        } catch (e) {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.connected_apps.delete.flash.error')
            );
        }
    }, [deleteApp, notify, translate, history, generateUrl]);

    const handleCancel = useCallback(() => {
        history.push(
            generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
                connectionCode: connectionCode,
            })
        );
    }, [history, generateUrl, connectionCode]);

    return (
        <Modal onClose={handleCancel} illustration={<AppIllustration />} closeTitle={translate('pim_common.cancel')}>
            <Subtitle>{translate('akeneo_connectivity.connection.connect.connected_apps.delete.subtitle')}</Subtitle>
            <Title>{translate('akeneo_connectivity.connection.connect.connected_apps.delete.title')}</Title>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.connected_apps.delete.description')}</p>
                <Link href={'https://help.akeneo.com/pim/articles/manage-your-apps.html#delete-an-app'}>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.delete.link')}
                </Link>
            </Helper>
            <Modal.BottomButtons>
                <Button onClick={handleCancel} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleClick} level='danger'>
                    {translate('pim_common.delete')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};

const Link = styled.a`
    color: ${({theme}) => theme.color.blue100};
    text-decoration: underline;
`;
