import React, {FC, useCallback, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {Button, Modal, ProgressIndicator, useProgress} from 'akeneo-design-system';
import styled from 'styled-components';
import {Permissions} from './Permissions';
import {PermissionsSummary} from './PermissionsSummary';
import {Authorizations} from './Authorizations';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {useTranslate} from '../../../shared/translate';
import {PermissionFormProvider, usePermissionFormRegistry} from '../../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {useConfirmHandler} from './useConfirmHandler';
import loaderImage from '../../../common/assets/illustrations/main-loader.gif';

const Content = styled.div`
    display: grid;
    grid-template-columns: 260px 593px;
    grid-template-areas: 'LOGO INFO';
`;
const LogoContainer = styled.div`
    grid-area: LOGO;
    padding-right: 40px;
`;
const Logo = styled.img`
    margin: auto;
    max-height: 220px;
    max-width: 220px;
`;

const StyledActionButton = styled(Button)`
    position: fixed;
    top: 40px;
    right: 40px;
`;
const PreviousButton = styled(Button)`
    position: fixed;
    top: 40px;
    left: 80px;
`;
const ProgressIndicatorContainer = styled(ProgressIndicator)`
    width: 456px;
    height: 70px;
    position: fixed;
    bottom: 20px;
`;
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

interface Props {
    clientId: string;
}

export const AppWizardWithSteps: FC<Props> = ({clientId}) => {
    const translate = useTranslate();
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const steps: string[] = ['authorizations', 'permissions', 'summary'];
    const [isCurrent, next, previous] = useProgress(steps);

    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<PermissionsByProviderKey>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => setProviders(providers));
    }, []);

    useEffect(() => {
        fetchWizardData().then(setWizardData);
    }, [fetchWizardData]);

    const redirectToMarketplace = useCallback(() => {
        history.push('/connect/marketplace');
    }, [history]);

    const handleSetProviderPermissions = useCallback(
        (providerKey: string, providerPermissions: object) => {
            setPermissions(state => ({...state, [providerKey]: providerPermissions}));
        },
        [setPermissions],
    );

    const {confirm, processing} = useConfirmHandler(clientId, providers, permissions);

    if (wizardData === null) {
        return null;
    }

    if (processing) {
        return (
            <FullScreen>
                <Loader>
                    <h3>
                        {translate('akeneo_connectivity.connection.connect.apps.loader.message')}
                    </h3>
                    <img src={loaderImage} />
                </Loader>
            </FullScreen>
        );
    }

    return (
        <Modal
            onClose={redirectToMarketplace}
            closeTitle={translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
        >
            {!isCurrent('authorizations') && (
                <PreviousButton level={'tertiary'} onClick={previous}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.action.previous')}
                </PreviousButton>
            )}

            {isCurrent('authorizations') && (
                <StyledActionButton onClick={next}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')}
                </StyledActionButton>
            )}
            {isCurrent('permissions') && (
                <StyledActionButton onClick={next}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.action.next')}
                </StyledActionButton>
            )}
            {isCurrent('summary') && (
                <StyledActionButton onClick={confirm}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')}
                </StyledActionButton>
            )}

            <Content>
                <LogoContainer>
                    <Logo src={wizardData.appLogo} alt={wizardData.appName} />
                </LogoContainer>
                {isCurrent('authorizations') && (
                    <Authorizations appName={wizardData.appName} scopeMessages={wizardData.scopeMessages} />
                )}
                {isCurrent('permissions') && (
                    <Permissions
                        appName={wizardData.appName}
                        providers={providers}
                        setProviderPermissions={handleSetProviderPermissions}
                        permissions={permissions}
                    />
                )}
                {isCurrent('summary') && (
                    <PermissionsSummary appName={wizardData.appName} providers={providers} permissions={permissions} />
                )}
            </Content>

            <ProgressIndicatorContainer>
                {steps.map(step => (
                    <ProgressIndicator.Step key={step} current={isCurrent(step)}>
                        {translate(`akeneo_connectivity.connection.connect.apps.wizard.progress.${step}`)}
                    </ProgressIndicator.Step>
                ))}
            </ProgressIndicatorContainer>
        </Modal>
    );
};
