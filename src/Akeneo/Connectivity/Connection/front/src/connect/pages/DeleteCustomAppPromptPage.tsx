import React, {FC, useCallback} from 'react';
import {useHistory, useParams} from 'react-router';
import {AppIllustration, Button, getColor, getFontSize, Modal} from 'akeneo-design-system';
import styled from '../../common/styled-with-theme';
import {useTranslate} from '../../shared/translate';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useDeleteCustomApp} from '../hooks/use-delete-custom-app';
import {useRoute} from '../../shared/router';

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

export const DeleteCustomAppPromptPage: FC = () => {
    const history = useHistory();
    const appStoreUrl = useRoute('akeneo_connectivity_connection_connect_marketplace');
    const translate = useTranslate();
    const notify = useNotify();

    const {customAppId} = useParams<{customAppId: string}>();
    const deleteCustomApp = useDeleteCustomApp(customAppId);

    const redirectToAppStore = useCallback(() => history.push(appStoreUrl), [history, appStoreUrl]);

    const handleClick = useCallback(() => {
        deleteCustomApp()
            .then(() =>
                notify(
                    NotificationLevel.SUCCESS,
                    translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.flash.success')
                )
            )
            .catch(() =>
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.flash.error')
                )
            )
            .finally(redirectToAppStore);
    }, [deleteCustomApp, notify, translate, redirectToAppStore]);

    return (
        <Modal
            onClose={redirectToAppStore}
            illustration={<AppIllustration />}
            closeTitle={translate('pim_common.cancel')}
        >
            <Subtitle>{translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.subtitle')}</Subtitle>
            <Title>{translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.title')}</Title>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.description')}</p>
                <p>{translate('akeneo_connectivity.connection.connect.custom_apps.delete_modal.warning')}</p>
            </Helper>
            <Modal.BottomButtons>
                <Button onClick={redirectToAppStore} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleClick} level='danger'>
                    {translate('pim_common.delete')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};
