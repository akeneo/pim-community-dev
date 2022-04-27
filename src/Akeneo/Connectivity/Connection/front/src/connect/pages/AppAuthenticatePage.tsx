import React, {FC} from 'react';
import {useLocation} from 'react-router-dom';
import {AuthenticationModal} from '../components/AppWizard/AuthenticationModal';
import {useHistory} from 'react-router';

const useQuery = () => {
    const {search} = useLocation();

    return React.useMemo(() => new URLSearchParams(search), [search]);
};

export const AppAuthenticatePage: FC = () => {
    const history = useHistory();
    const query = useQuery();

    const clientId = query.get('client_id');
    const newAuthenticationScopes = query.get('new_authentication_scopes')?.split(',');

    if (!clientId || !newAuthenticationScopes) {
        history.push('/connect/app-store');

        return null;
    }

    return (
        <AuthenticationModal
            clientId={clientId}
            newAuthenticationScopes={newAuthenticationScopes as Array<'email' | 'profile'>}
        />
    );
};
