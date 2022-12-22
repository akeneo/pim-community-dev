import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {Step, WizardModal} from './WizardModal';
import {FullScreenLoader} from './FullScreenLoader';
import {useConfirmHandler} from '../../hooks/use-confirm-handler';
import {useFeatureFlags} from '../../../shared/feature-flags';
import {Permissions} from './steps/Permissions';
import {PermissionsSummary} from './steps/PermissionsSummary';
import ScopeMessage from '../../../model/Apps/scope-message';

interface Props {
    clientId: string;
}

export const AppWizard: FC<Props> = ({clientId}) => {
    const featureFlags = useFeatureFlags();
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const [steps, setSteps] = useState<Step[]>([]);
    const [authenticationScopesConsentGiven, setAuthenticationScopesConsent] = useState<boolean>(false);
    const [certificationConsentGiven, setCertificationConsent] = useState<boolean>(false);

    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => setProviders(providers));
    }, []);

    useEffect(() => {
        fetchWizardData().then(wizardData => {
            const steps: Step[] = [];

            const supportsPermissions = true === featureFlags.isEnabled('connect_app_with_permissions');
            const shouldDisplayPermissionsStep =
                undefined !==
                wizardData.scopeMessages.find((scopeMessage: ScopeMessage) => {
                    return 'products' === scopeMessage.entities;
                });
            const requiresAuthentication = wizardData.authenticationScopes.length > 0;
            const isAlreadyConnected = wizardData.oldScopeMessages !== null;

            if (requiresAuthentication) {
                steps.push({
                    name: 'authentication',
                    requires_explicit_approval: true,
                });
            }

            steps.push({
                name: 'authorizations',
                requires_explicit_approval: true,
            });

            if (!isAlreadyConnected && supportsPermissions && shouldDisplayPermissionsStep) {
                steps.push({
                    name: 'permissions',
                    requires_explicit_approval: false,
                });
                steps.push({
                    name: 'summary',
                    requires_explicit_approval: false,
                });
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

    const permissionsAreEditable = steps.find(step => step.name === 'permissions') !== undefined;

    // @todo rethink useConfirmHandler signature
    const {confirm, processing} = useConfirmHandler(
        clientId,
        permissionsAreEditable ? providers : [],
        permissionsAreEditable ? permissions : {}
    );

    if (wizardData === null) {
        return null;
    }

    const onlyDisplayViewPermissions =
        undefined ===
        wizardData.scopeMessages.find((scopeMessage: ScopeMessage) => {
            return 'products' === scopeMessage.entities && ['edit', 'delete'].includes(scopeMessage.type);
        });

    if (processing) {
        return <FullScreenLoader />;
    }

    const certificationConsentRequired = wizardData.appIsCertified && !certificationConsentGiven;

    return (
        <WizardModal
            appLogo={wizardData.appLogo}
            appName={wizardData.appName}
            onClose={redirectToMarketplace}
            onConfirm={confirm}
            steps={steps}
            maxAllowedStep={
                (!authenticationScopesConsentGiven && wizardData.displayCheckboxConsent) || certificationConsentRequired
                    ? 'authorizations'
                    : null
            }
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
                            displayConsent={false}
                            displayCheckboxConsent={wizardData.displayCheckboxConsent}
                        />
                    )}
                    {step.name === 'authorizations' && (
                        <Authorizations
                            appName={wizardData.appName}
                            scopeMessages={wizardData.scopeMessages}
                            oldScopeMessages={wizardData.oldScopeMessages}
                            appUrl={wizardData.appUrl}
                            scopesConsentGiven={authenticationScopesConsentGiven}
                            setScopesConsent={setAuthenticationScopesConsent}
                            certificationConsentGiven={certificationConsentGiven}
                            setCertificationConsent={setCertificationConsent}
                            displayCertificationConsent={wizardData.appIsCertified}
                            displayCheckboxConsent={wizardData.displayCheckboxConsent}
                        />
                    )}
                    {step.name === 'permissions' && (
                        <Permissions
                            appName={wizardData.appName}
                            providers={providers}
                            setProviderPermissions={handleSetProviderPermissions}
                            permissions={permissions}
                            onlyDisplayViewPermissions={onlyDisplayViewPermissions}
                        />
                    )}
                    {step.name === 'summary' && (
                        <PermissionsSummary
                            appName={wizardData.appName}
                            providers={providers}
                            permissions={permissions}
                            onlyDisplayViewPermissions={onlyDisplayViewPermissions}
                        />
                    )}
                </>
            )}
        </WizardModal>
    );
};
