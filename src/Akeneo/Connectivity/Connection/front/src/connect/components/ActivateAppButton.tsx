import React, {FC} from 'react';
import {useSecurity} from '../../shared/security';
import {useTranslate} from '../../shared/translate';
import {Button} from 'akeneo-design-system';
import {useRouter} from "../../shared/router/use-router";
import {useNotify} from "../../shared/notify";

export const ActivateAppButton: FC<{id: string}> = ({id}) => {
    const translate = useTranslate();
    const security = useSecurity();
    const generateUrl = useRouter();
    const notify = useNotify();
    const isAuthorized = !security.isGranted('akeneo_connectivity_connection_manage_apps');

    const url = `#${generateUrl('akeneo_connectivity_connection_connect_apps_activate', {
        id: id,
    })}`;

    return (
        <Button href={url} target="_blank" level='primary' disabled={isAuthorized}>
            {translate('akeneo_connectivity.connection.connect.marketplace.card.connect')}
        </Button>
    );
};
