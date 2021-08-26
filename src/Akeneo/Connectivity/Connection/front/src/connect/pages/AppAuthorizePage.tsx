import React, {FC} from 'react';
import {useLocation} from 'react-router-dom';
import {AuthorizeClientError} from '../components/AuthorizeClientError';
import {AppWizard} from '../components/AppWizard/AppWizard';

export const AppAuthorizePage: FC = () => {
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const error = query.get('error');
    const clientId = query.get('client_id');

    if (null === clientId) {
        return (
            <AuthorizeClientError error={'akeneo_connectivity.connection.connect.apps.authorize.error.no_client_id'} />
        );
    }

    if (null !== error) {
        return <AuthorizeClientError error={error} />;
    }

    return <AppWizard clientId={clientId} />;
};
