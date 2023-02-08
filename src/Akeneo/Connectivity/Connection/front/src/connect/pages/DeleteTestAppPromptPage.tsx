import React, {FC, useCallback} from 'react';
import {useHistory, useParams} from 'react-router';
import {Modal, AppIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useDeleteTestApp} from '../hooks/use-delete-test-app';
import {useAppDeveloperMode} from '../hooks/use-app-developer-mode';

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

export const DeleteTestAppPromptPage: FC = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const notify = useNotify();
    const isAppDeveloperModeEnabled = useAppDeveloperMode();

    const {customAppId} = useParams<{customAppId: string}>();
    const deleteTestApp = useDeleteTestApp(customAppId);

    if (!isAppDeveloperModeEnabled) {
        history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }

    const handleClick = useCallback(() => {
        deleteTestApp()
            .then(() =>
                notify(
                    NotificationLevel.SUCCESS,
                    translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.flash.success')
                )
            )
            .catch(() =>
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.flash.error')
                )
            )
            .finally(() => history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace')));
    }, [deleteTestApp, notify, translate, history, generateUrl]);

    const handleCancel = useCallback(() => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_marketplace'));
    }, [history, generateUrl]);

    return (
        <Modal onClose={handleCancel} illustration={<AppIllustration />} closeTitle={translate('pim_common.cancel')}>
            <Subtitle>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.subtitle')}
            </Subtitle>
            <Title>{translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.title')}</Title>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.description')}</p>
                <p>{translate('akeneo_connectivity.connection.connect.marketplace.test_apps.delete.warning')}</p>
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
