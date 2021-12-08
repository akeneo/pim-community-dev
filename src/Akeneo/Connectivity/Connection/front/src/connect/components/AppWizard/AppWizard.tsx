import React, {FC, useCallback, useEffect, useState} from 'react';
import styled from 'styled-components';
import {Button, getColor, getFontSize, Modal} from 'akeneo-design-system';
import {useHistory} from 'react-router';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {useTranslate} from '../../../shared/translate';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {ScopeListContainer} from './ScopeListContainer';
import {useConfirmAuthorization} from '../../hooks/use-confirm-authorization';
import {NotificationLevel, useNotify} from '../../../shared/notify';

const Content = styled.div`
    display: grid;
    grid-template-columns: 260px 593px;
    grid-template-areas: 'LOGO INFO';
`;
const LogoContainer = styled.div`
    grid-area: LOGO;
    padding-right: 40px;
`;
const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 0 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
`;
const Actions = styled.div`
    margin-top: 20px;
`;

const LogoFrame = styled.div`
    width: 220px;
    height: 220px;
    border: 1px ${getColor('grey', 40)} solid;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
`;

const Logo = styled.img`
    max-width: 220px;
    max-height: 220px;
`;

const Connect = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

const ActionButton = styled(Button)`
    margin-right: 10px;
`;

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

    useEffect(() => {
        fetchWizardData().then(setWizardData);
    }, [fetchWizardData]);

    const redirectToMarketplace = useCallback(() => {
        history.push('/connect/marketplace');
    }, [history]);

    const handleConfirm = useCallback(async () => {
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

    return (
        <Modal
            onClose={redirectToMarketplace}
            closeTitle={translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
        >
            <Content>
                <LogoContainer>
                    <LogoFrame>
                        <Logo src={wizardData.appLogo} alt={wizardData.appName} />
                    </LogoFrame>
                </LogoContainer>
                <InfoContainer>
                    <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
                    <ScopeListContainer appName={wizardData.appName} scopeMessages={wizardData.scopeMessages} />
                    <Actions>
                        <ActionButton level={'tertiary'} onClick={redirectToMarketplace}>
                            {translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
                        </ActionButton>
                        <ActionButton onClick={handleConfirm}>
                            {translate('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')}
                        </ActionButton>
                    </Actions>
                </InfoContainer>
            </Content>
        </Modal>
    );
};
