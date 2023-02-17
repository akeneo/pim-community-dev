import {Button, ExternalLinkIcon} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../shared/translate';
import React, {FC} from 'react';
import {useRouter} from '../../../shared/router/use-router';
import {useSecurity} from '../../../shared/security';
import {ConnectedApp} from '../../../model/Apps/connected-app';

type Props = {
    connectedApp: ConnectedApp;
};

export const OpenAppButton: FC<Props> = ({connectedApp}) => {
    const security = useSecurity();
    const generateUrl = useRouter();
    const translate = useTranslate();

    const openConnectedAppUrl = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps_open', {
        connectionCode: connectedApp.connection_code,
    })}`;

    const canOpenApp = security.isGranted('akeneo_connectivity_connection_open_apps');

    return (
        <Button
            level={connectedApp.is_pending || connectedApp.has_outdated_scopes ? 'warning' : 'secondary'}
            href={openConnectedAppUrl}
            disabled={!canOpenApp}
            target='_blank'
        >
            <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label' />
            <ExternalLinkIcon
                title={translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.icon_alt'
                )}
                height={'13px'}
            />
        </Button>
    );
};
