import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import {FullScreenError} from '@akeneo-pim-community/shared';
import {useParams} from 'react-router-dom';
import {useConnectedApp} from '../hooks/use-connected-app';
import {ConnectedAppCatalogContainer} from '../components/ConnectedApp/Catalog/ConnectedAppCatalogContainer';
import {HttpError} from '../../model/http-error.enum';
import {useCatalog} from '@akeneo-pim-community/catalogs';

export const ConnectedAppCatalogPage: FC = () => {
    const translate = useTranslate();
    const {connectionCode, catalogId} = useParams<{connectionCode: string; catalogId: string}>();

    const {
        loading: connectedAppLoading,
        error: connectedAppError,
        payload: connectedApp,
    } = useConnectedApp(connectionCode);
    const {isLoading: catalogLoading, isError: isCatalogError, data: catalog} = useCatalog(catalogId);

    if (connectedAppLoading || catalogLoading) {
        return null;
    }

    if (HttpError.Forbidden === connectedAppError) {
        return (
            <FullScreenError
                title={translate('error.exception', {status_code: '403'})}
                message={translate('error.forbidden')}
                code={403}
            />
        );
    }

    if (HttpError.NotFound === connectedAppError) {
        return (
            <FullScreenError
                title={translate('error.exception', {status_code: '404'})}
                message={translate('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')}
                code={404}
            />
        );
    }

    if (isCatalogError) {
        // @todo manage more precisely useCatalog 4XX errors
        return (
            <FullScreenError
                title={translate('error.exception', {status_code: '404'})}
                message={translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found'
                )}
                code={404}
            />
        );
    }

    if (null === connectedApp || undefined === catalog) {
        throw Error('Connected app and catalog should not be null');
    }

    if (catalog.owner_username !== connectedApp.connection_username) {
        return (
            <FullScreenError
                title={translate('error.exception', {status_code: '404'})}
                message={translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found'
                )}
                code={404}
            />
        );
    }

    return <ConnectedAppCatalogContainer connectedApp={connectedApp} catalog={catalog} />;
};
