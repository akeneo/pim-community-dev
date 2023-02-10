import React, {FC, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {useTranslate} from '../../../shared/translate';
import {useConfirmAuthentication} from '../../hooks/use-confirm-authentication';
import {Authentication} from './steps/Authentication/Authentication';
import {WizardModal} from './WizardModal';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {AppWizardData} from '../../../model/Apps/wizard-data';

type Props = {
    clientId: string;
};

export const AuthenticationModal: FC<Props> = ({clientId}) => {
    const translate = useTranslate();
    const notify = useNotify();
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
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

    useEffect(() => {
        fetchWizardData().then(setWizardData);
    }, [fetchWizardData]);

    if (!wizardData) {
        return null;
    }

    return (
        <WizardModal
            appLogo={wizardData.appLogo}
            appName={wizardData.appName}
            onConfirm={handleConfirm}
            onClose={handleClose}
            maxAllowedStep={!scopesConsentGiven && wizardData.displayCheckboxConsent ? 'authentication' : null}
            steps={[
                {
                    name: 'authentication',
                    requires_explicit_approval: true,
                },
            ]}
        >
            {() => (
                <Authentication
                    appName={wizardData.appName}
                    scopes={wizardData.authenticationScopes}
                    oldScopes={wizardData.oldAuthenticationScopes}
                    appUrl={wizardData.appUrl}
                    scopesConsentGiven={scopesConsentGiven}
                    setScopesConsent={setScopesConsent}
                    displayConsent={true}
                    displayCheckboxConsent={wizardData.displayCheckboxConsent}
                />
            )}
        </WizardModal>
    );
};
