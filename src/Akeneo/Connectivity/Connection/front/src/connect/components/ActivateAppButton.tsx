import React, {FC} from 'react';
import {useSecurity} from '../../shared/security';
import {useTranslate} from '../../shared/translate';
import {Button} from 'akeneo-design-system';
import {useRouter} from "../../shared/router/use-router";
import {NotificationLevel, useNotify} from "../../shared/notify";

export const ActivateAppButton: FC<{id: string}> = ({id}) => {
    const translate = useTranslate();
    const security = useSecurity();
    const generateUrl = useRouter();
    const notify = useNotify();
    const isAuthorized = !security.isGranted('akeneo_connectivity_connection_manage_apps');

    const handleClick = () => {
        const url = generateUrl('akeneo_connectivity_connection_apps_rest_get_app_activate_url', {
            'id': id,
        });

        fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        })
            .then(response => response.json())
            .then(response => {
                window.open(response.url);
            })
            .catch(() => {
                notify(NotificationLevel.ERROR, translate('akeneo_connectivity.connection.connect.apps.activate.flash.error'))
            });
    };

    return (
        <Button onClick={handleClick} level='primary' disabled={isAuthorized}>
            {translate('akeneo_connectivity.connection.connect.marketplace.card.connect')}
        </Button>
    );
};
