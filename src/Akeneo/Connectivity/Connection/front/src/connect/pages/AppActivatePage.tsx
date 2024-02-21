import React, {FC} from 'react';
import {useRouter} from '../../shared/router/use-router';
import {useTranslate} from '../../shared/translate';
import {NotificationLevel, useNotify} from '../../shared/notify';
import styled from 'styled-components';
import {useLocation} from 'react-router-dom';

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
`;

export const AppActivatePage: FC = () => {
    const generateUrl = useRouter();
    const translate = useTranslate();
    const notify = useNotify();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const id = query.get('id');

    if (typeof id !== 'string') {
        notify(NotificationLevel.ERROR, translate('akeneo_connectivity.connection.connect.apps.activate.flash.error'));

        return null;
    }

    const url = generateUrl('akeneo_connectivity_connection_apps_rest_get_app_activate_url', {
        id: id,
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
                translate('akeneo_connectivity.connection.connect.apps.activate.flash.error')
            );
        });

    return <FullScreen />;
};
