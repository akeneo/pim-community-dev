import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../shared/translate';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useParams} from 'react-router-dom';
import {useFetchData} from '../../shared/hooks/use-fetch-data';

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
    const translate = useTranslate();
    const notify = useNotify();

    const {connectionCode} = useParams<{connectionCode: string}>();

    const {isLoading, data} = useFetchData<{url: string}>('akeneo_connectivity_connection_apps_rest_get_open_app_url', {
        connectionCode,
    });

    if (!isLoading) {
        if (data === undefined) {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.connected_apps.open.flash.error')
            );
        } else {
            window.location.replace(data.url);
        }
    }

    return <FullScreen />;
};
