import React, {FC} from 'react';
import {useLocation} from 'react-router-dom';
import {AuthorizeClientError} from '../components/AuthorizeClientError';
import {AppWizard} from '../components/AppWizard/AppWizard';
import {useHistory} from 'react-router';
import {AppWizardWithPermissions} from '../components/AppWizard/AppWizardWithPermissions';
import {useFeatureFlags} from '../../shared/feature-flags';

export const AppAuthorizePage: FC = () => {
    const history = useHistory();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const error = query.get('error');
    const clientId = query.get('client_id');
    const featureFlags = useFeatureFlags();

    if (null !== error) {
        return <AuthorizeClientError error={error} />;
    }

    if (null === clientId) {
        history.push('/connect/app-store');
        return null;
    }

    if (true === featureFlags.isEnabled('connect_app_with_permissions')) {
        return <AppWizardWithPermissions clientId={clientId} />;
    }

    return <AppWizard clientId={clientId} />;
};
