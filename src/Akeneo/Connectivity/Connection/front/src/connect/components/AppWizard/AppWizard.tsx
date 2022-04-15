import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {WizardModal} from './WizardModal';
import {FullScreenLoader} from './FullScreenLoader';
import {useConfirmHandler} from '../../hooks/use-confirm-handler';

type Step = {
    name: 'authentication' | 'authorizations';
    action: 'next' | 'allow_and_next' | 'confirm' | 'allow_and_finish';
};

interface Props {
    clientId: string;
}

export const AppWizard: FC<Props> = ({clientId}) => {
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const [steps, setSteps] = useState<Step[]>([]);
    const [authenticationScopesConsentGiven, setAuthenticationScopesConsent] = useState<boolean>(false);

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

    const {confirm, processing} = useConfirmHandler(clientId, [], {});

    if (wizardData === null) {
        return null;
    }

    if (processing) {
        return <FullScreenLoader />;
    }

    const userConsentRequired = wizardData.authenticationScopes.length !== 0 && !authenticationScopesConsentGiven;

    return (
        <WizardModal
            appLogo={wizardData.appLogo}
            appName={wizardData.appName}
            onClose={redirectToMarketplace}
            onConfirm={confirm}
            steps={steps}
            maxAllowedStep={userConsentRequired ? 'authentication' : null}
        >
            {step => (
                <>
                    {step.name === 'authentication' && (
                        <Authentication
                            appName={wizardData.appName}
                            scopes={wizardData.authenticationScopes}
                            oldScopes={wizardData.oldAuthenticationScopes}
                            appUrl={wizardData.appUrl}
                            scopesConsentGiven={authenticationScopesConsentGiven}
                            setScopesConsent={setAuthenticationScopesConsent}
                        />
                    )}
                    {step.name === 'authorizations' && (
                        <Authorizations
                            appName={wizardData.appName}
                            scopeMessages={wizardData.scopeMessages}
                            oldScopeMessages={wizardData.oldScopeMessages}
                        />
                    )}
                </>
            )}
        </WizardModal>
    );
};
