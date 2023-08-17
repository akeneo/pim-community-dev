import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {useTranslate} from '../../../shared/translate';
import {useConfirmAuthorization} from '../../hooks/use-confirm-authorization';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {WizardModal} from './WizardModal';
import {FullScreenLoader} from './FullscreenLoader';

type Step = {
    name: 'authentication' | 'authorizations';
    action: 'next' | 'allow_and_next' | 'confirm' | 'allow_and_finish';
};

interface Props {
    clientId: string;
}

export const AppWizard: FC<Props> = ({clientId}) => {
    const translate = useTranslate();
    const notify = useNotify();
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const confirmAuthorization = useConfirmAuthorization(clientId);
    const [steps, setSteps] = useState<Step[]>([]);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        fetchWizardData().then(wizardData => {
            if (wizardData.authenticationScopes.length === 0) {
                setSteps([
                    {
                        name: 'authorizations',
                        action: 'confirm',
                    },
                ]);
            } else {
                setSteps([
                    {
                        name: 'authentication',
                        action: 'allow_and_next',
                    },
                    {
                        name: 'authorizations',
                        action: 'allow_and_finish',
                    },
                ]);
            }

            setWizardData(wizardData);
        });
    }, [fetchWizardData]);

    const redirectToMarketplace = useCallback(() => {
        history.push('/connect/app-store');
    }, [history]);

    const handleConfirm = useCallback(async () => {
        setProcessing(true);
        try {
            const {redirectUrl} = await confirmAuthorization();
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
    }, [confirmAuthorization, notify, translate]);

    if (wizardData === null) {
        return null;
    }

    if (processing) {
        return <FullScreenLoader />;
    }

    return (
        <WizardModal
            appLogo={wizardData.appLogo}
            appName={wizardData.appName}
            onClose={redirectToMarketplace}
            onConfirm={handleConfirm}
            steps={steps}
        >
            {step => (
                <>
                    {step.name === 'authentication' && (
                        <Authentication appName={wizardData.appName} scopes={wizardData.authenticationScopes} />
                    )}
                    {step.name === 'authorizations' && (
                        <Authorizations appName={wizardData.appName} scopeMessages={wizardData.scopeMessages} />
                    )}
                </>
            )}
        </WizardModal>
    );
};
