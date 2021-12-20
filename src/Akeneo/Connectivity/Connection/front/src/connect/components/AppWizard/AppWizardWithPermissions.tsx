import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {useTranslate} from '../../../shared/translate';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {Authentication} from './steps/Authentication/Authentication';
import {Authorizations} from './steps/Authorizations';
import {Permissions} from './steps/Permissions';
import {PermissionsSummary} from './steps/PermissionsSummary';
import {WizardModal} from './WizardModal';
import styled from 'styled-components';
import {useConfirmHandler} from '../../hooks/use-confirm-handler';
import loaderImage from '../../../common/assets/illustrations/main-loader.gif';

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
`;
const Loader = styled.div`
    width: 940px;
    font-size: 28px;
    display: block;
    margin: 200px auto 0;
    text-align: center;
    line-height: 40px;
`;

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
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const [steps, setSteps] = useState<Step[]>([]);

    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

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
        history.push('/connect/marketplace');
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
        return (
            <FullScreen>
                <Loader>
                    <h3>{translate('akeneo_connectivity.connection.connect.apps.loader.message')}</h3>
                    <img src={loaderImage} />
                </Loader>
            </FullScreen>
        );
    }

    return (
        <WizardModal
            appLogo={wizardData.appLogo}
            appName={wizardData.appName}
            onClose={redirectToMarketplace}
            onConfirm={confirm}
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
