import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {useTranslate} from '../../../shared/translate';
import {useConfirmAuthorization} from '../../hooks/use-confirm-authorization';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {Permissions} from './steps/Permissions';
import {PermissionsSummary} from './steps/PermissionsSummary';
import {WizardModal} from './WizardModal';
import {FullScreenLoader} from './FullscreenLoader';

type Step = {
    name: 'authentication' | 'authorizations' | 'permissions' | 'summary';
    action: 'next' | 'allow_and_next' | 'confirm' | 'allow_and_finish';
};

interface Props {
    clientId: string;
}

export const AppWizardWithPermissions: FC<Props> = ({clientId}) => {
    const translate = useTranslate();
    const history = useHistory();
    const notify = useNotify();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const [steps, setSteps] = useState<Step[]>([]);

    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});
    const [processing, setProcessing] = useState(false);
    const confirmAuthorization = useConfirmAuthorization(clientId);

    useEffect(() => {
        permissionFormRegistry.all().then(providers => setProviders(providers));
    }, []);

    useEffect(() => {
        fetchWizardData().then(wizardData => {
            const steps: Step[] = [
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
                },
            ];
            if (wizardData.authenticationScopes.length > 0) {
                steps.unshift({
                    name: 'authentication',
                    action: 'allow_and_next',
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

    const notifyPermissionProviderError = useCallback(
        (entity: string): void => {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.apps.flash.permissions_error.description'),
                {
                    titleMessage: translate(
                        'akeneo_connectivity.connection.connect.apps.flash.permissions_error.title',
                        {
                            entity: entity,
                        }
                    ),
                }
            );
        },
        [notify, translate]
    );

    const handleConfirm = useCallback(async () => {
        let userGroup;
        let redirectUrl;

        setProcessing(true);

        try {
            ({userGroup, redirectUrl} = await confirmAuthorization());
        } catch (e) {
            notify(
                NotificationLevel.ERROR,
                translate('akeneo_connectivity.connection.connect.apps.wizard.flash.error')
            );
            return;
        }

        for (const provider of providers) {
            try {
                await provider.save(userGroup, permissions[provider.key]);
            } catch {
                notifyPermissionProviderError(provider.label);
            }
        }

        notify(
            NotificationLevel.SUCCESS,
            translate('akeneo_connectivity.connection.connect.apps.wizard.flash.success')
        );

        window.location.assign(redirectUrl);
    }, [confirmAuthorization, notify, translate, providers, permissions, notifyPermissionProviderError]);

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
                    {step.name === 'permissions' && (
                        <Permissions
                            appName={wizardData.appName}
                            providers={providers}
                            setProviderPermissions={handleSetProviderPermissions}
                            permissions={permissions}
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
