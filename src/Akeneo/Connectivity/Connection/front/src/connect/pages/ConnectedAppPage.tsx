import React, {FC, useEffect, useState} from 'react';
import {useTranslate} from '../../shared/translate';
import {useFeatureFlags} from '../../shared/feature-flags';
import {useFetchConnectedApp} from '../hooks/use-fetch-connected-app';
import {ConnectedApp} from '../../model/Apps/connected-app';
import {FullScreenError} from '@akeneo-pim-community/shared';
import {ConnectedAppContainerIsLoading} from '../components/ConnectedApp/ConnectedAppContainerIsLoading';
import {ConnectedAppContainer} from '../components/ConnectedApp/ConnectedAppContainer';
import {useParams} from 'react-router-dom';

export const ConnectedAppPage: FC = () => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const fetchConnectedApp = useFetchConnectedApp(connectionCode);
    const [connectedApp, setConnectedApp] = useState<ConnectedApp | null | false>(null);

    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedApp(false);
            return;
        }

        fetchConnectedApp()
            .then(setConnectedApp)
            .catch(() => setConnectedApp(false));
    }, [fetchConnectedApp]);

    return (
        <>
            {null === connectedApp && <ConnectedAppContainerIsLoading />}
            {false === connectedApp && (
                <FullScreenError
                    title={translate('error.exception', {status_code: '404'})}
                    message={translate('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')}
                    code={404}
                />
            )}
            {false !== connectedApp && null !== connectedApp && <ConnectedAppContainer connectedApp={connectedApp} />}
        </>
    );
};
