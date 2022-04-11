import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {Permissions} from './steps/Permissions';
import {PermissionsSummary} from './steps/PermissionsSummary';
import {WizardModal} from './WizardModal';
import {useConfirmHandler} from '../../hooks/use-confirm-handler';
import {FullScreenLoader} from './FullScreenLoader';
import ScopeMessage from '../../../model/Apps/scope-message';

type Step = {
    name: 'authentication' | 'authorizations' | 'permissions' | 'summary';
    action: 'next' | 'allow_and_next' | 'confirm' | 'allow_and_finish';
};

interface Props {
    clientId: string;
}

export const AppWizardWithPermissions: FC<Props> = ({clientId}) => {
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const [steps, setSteps] = useState<Step[]>([]);
    const [authenticationScopesConsentGiven, setAuthenticationScopesConsent] = useState<boolean>(false);

    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => setProviders(providers));
    }, []);

    useEffect(() => {
        fetchWizardData().then(wizardData => {
            const steps: Step[] = [];

            if (wizardData.authenticationScopes.length > 0) {
                steps.push({
                    name: 'authentication',
                    action: 'allow_and_next',
                });
            }

            const displayPermissionsStep =
                undefined !==
                wizardData.scopeMessages.find((scopeMessage: ScopeMessage) => {
                    return 'products' === scopeMessage.entities;
                });

            if (!displayPermissionsStep) {
                steps.push({
                    name: 'authorizations',
                    action: 'confirm',
                });
            } else {
                steps.push(
                    {
                        name: 'authorizations',
                        action: 'allow_and_next',
                    },
                    {
                        name: 'permissions',
                        action: 'next',
                    },
                    {
                        name: 'summary',
                        action: 'confirm',
                    }
                );
            }

            setSteps(steps);

            setWizardData(wizardData);
        });
    }, [fetchWizardData]);

    const redirectToMarketplace = useCallback(() => {
        history.push('/connect/app-store');
    }, [history]);

    const handleSetProviderPermissions = useCallback(
        (providerKey: string, providerPermissions: object) => {
            setPermissions(state => ({...state, [providerKey]: providerPermissions}));
        },
        [setPermissions]
    );

    const {confirm, processing} = useConfirmHandler(clientId, providers, permissions);

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
                            appUrl={wizardData.appUrl}
                            scopesConsentGiven={authenticationScopesConsentGiven}
                            setScopesConsent={setAuthenticationScopesConsent}
                        />
                    )}
                    {step.name === 'authorizations' && (
                        <Authorizations appName={wizardData.appName} scopeMessages={wizardData.scopeMessages} />
                    )}
                    {step.name === 'permissions' && (
                        <Permissions
                            appName={wizardData.appName}
                            providers={providers}
                            setProviderPermissions={handleSetProviderPermissions}
                            permissions={permissions}
                            scopeMessages={wizardData.scopeMessages}
                        />
                    )}
                    {step.name === 'summary' && (
                        <PermissionsSummary
                            appName={wizardData.appName}
                            providers={providers}
                            permissions={permissions}
                        />
                    )}
                </>
            )}
        </WizardModal>
    );
};
