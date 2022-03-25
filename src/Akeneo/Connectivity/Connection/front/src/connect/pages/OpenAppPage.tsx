import React, {FC} from 'react';
import styled from 'styled-components';
import {useRouter} from '../../shared/router/use-router';
import {useTranslate} from '../../shared/translate';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useParams} from 'react-router-dom';

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
`;

export const OpenAppPage: FC = () => {
    const generateUrl = useRouter();
    const translate = useTranslate();
    const notify = useNotify();

    const {connectionCode} = useParams<{ connectionCode: string; }>();

    const url = generateUrl('akeneo_connectivity_connection_apps_rest_get_open_app_url', {
        connectionCode: connectionCode,
    });

    fetch(url, {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
    })
        .then(response => response.json())
        .then(response => {
            window.location.replace(response.url);
        })
        .catch(() => {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.connected_apps.open.flash.error')
            );
        });

    return <FullScreen />;
};
