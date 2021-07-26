import React, {FC} from 'react';
import {useSecurity} from '../../shared/security';
import {useTranslate} from '../../shared/translate';
import {Button} from 'akeneo-design-system';

export const ActivateAppButton: FC<{url: string}> = ({url}) => {
    const translate = useTranslate();
    const security = useSecurity();
    const isAuthorized = !security.isGranted('akeneo_connectivity_connection_manage_apps');

    return (
        <Button level='primary' href={url} target='_self' disabled={isAuthorized}>
            {translate('akeneo_connectivity.connection.connect.marketplace.card.connect')}
        </Button>
    );
};
