import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import {FullScreenError} from '@akeneo-pim-community/shared';
import {ConnectedAppContainerIsLoading} from '../components/ConnectedApp/ConnectedAppContainerIsLoading';
import {ConnectedAppContainer} from '../components/ConnectedApp/ConnectedAppContainer';
import {useParams} from 'react-router-dom';
import {useConnectedApp} from '../hooks/use-connected-app';
import {HttpError} from '../../model/http-error.enum';

export const ConnectedAppPage: FC = () => {
    const translate = useTranslate();
    const {connectionCode} = useParams<{connectionCode: string}>();

    const {loading, error, payload: connectedApp} = useConnectedApp(connectionCode);

    return (
        <>
            {loading && <ConnectedAppContainerIsLoading />}
            {HttpError.Forbidden === error && (
                <FullScreenError
                    title={translate('error.exception', {status_code: '403'})}
                    message={translate('error.forbidden')}
                    code={403}
                />
            )}
            {HttpError.NotFound === error && (
                <FullScreenError
                    title={translate('error.exception', {status_code: '404'})}
                    message={translate('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')}
                    code={404}
                />
            )}
            {null !== connectedApp && <ConnectedAppContainer connectedApp={connectedApp} />}
        </>
    );
};
