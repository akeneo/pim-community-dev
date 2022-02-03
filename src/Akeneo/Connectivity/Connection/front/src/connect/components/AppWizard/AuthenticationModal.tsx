import React, {FC, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {App} from '../../../model/app';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {useTranslate} from '../../../shared/translate';
import {useConfirmAuthentication} from '../../hooks/use-confirm-authentication';
import {useFetchApps} from '../../hooks/use-fetch-apps';
import {Authentication} from './steps/Authentication/Authentication';
import {WizardModal} from './WizardModal';

const useFetchApp = (clientId: string) => {
    const fetchApps = useFetchApps();

    const [app, setApp] = useState<App | null>(null);
    useEffect(() => {
        fetchApps().then(apps => setApp(apps.apps.find(app => app.id === clientId) || null));
    }, [fetchApps, clientId]);

    return app;
};

type Props = {
    clientId: string;
    newAuthenticationScopes: Array<'email' | 'profile'>;
};

export const AuthenticationModal: FC<Props> = ({clientId, newAuthenticationScopes}) => {
    const translate = useTranslate();
    const notify = useNotify();
    const history = useHistory();
    const [scopesConsentGiven, setScopesConsent] = useState<boolean>(false);

    const confirmAuthentication = useConfirmAuthentication(clientId);
    const handleConfirm = async () => {
        try {
            const {redirectUrl} = await confirmAuthentication();
            notify(
                NotificationLevel.SUCCESS,
                translate('akeneo_connectivity.connection.connect.apps.wizard.flash.success')
            );
            window.location.assign(redirectUrl);
        } catch (e) {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.apps.wizard.flash.error')
            );
        }
    };

    const handleClose = () => history.push('/connect/connected-apps');

    const app = useFetchApp(clientId);
    if (!app) {
        return null;
    }

    console.log(scopesConsentGiven, '----------------------', !scopesConsentGiven ? 'authentication' : null);

    return (
        <WizardModal
            appLogo={app.logo}
            appName={app.name}
            onConfirm={handleConfirm}
            onClose={handleClose}
            maxAllowedStep={!scopesConsentGiven ? 'authentication' : null}
            steps={[
                {
                    name: 'authentication',
                    action: 'confirm',
                },
            ]}
        >
            {() => (
                <Authentication
                    appName={app.name}
                    scopes={newAuthenticationScopes}
                    appUrl={app.url}
                    scopesConsentGiven={scopesConsentGiven}
                    setScopesConsent={setScopesConsent}
                />
            )}
        </WizardModal>
    );
};
