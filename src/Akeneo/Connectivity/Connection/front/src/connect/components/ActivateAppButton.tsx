import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import {Button} from 'akeneo-design-system';
import {useRouter} from '../../shared/router/use-router';

type Props = {
    id: string;
    isConnected: boolean;
    isDisabled: boolean;
    isPending: boolean;
};

export const ActivateAppButton: FC<Props> = ({id, isConnected, isPending, isDisabled}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();

    if (isPending) {
        return (
            <Button level='primary' disabled ghost>
                {translate('akeneo_connectivity.connection.connect.marketplace.card.pending')}
            </Button>
        );
    }
    if (isConnected) {
        return (
            <Button level='primary' disabled>
                {translate('akeneo_connectivity.connection.connect.marketplace.card.connected')}
            </Button>
        );
    }

    const url = `#${generateUrl('akeneo_connectivity_connection_connect_apps_activate', {
        id: id,
    })}`;

    return (
        <Button href={url} target='_blank' level='primary' disabled={isDisabled}>
            {translate('akeneo_connectivity.connection.connect.marketplace.card.connect')}
        </Button>
    );
};
